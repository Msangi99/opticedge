<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Selcompay;
use App\Models\Setting;
use App\Services\DistributionSaleService;
use Bryceandy\Selcom\Facades\Selcom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SelcomController extends Controller
{
    /**
     * Use Selcom credentials from store settings (admin dashboard).
     * When all three are set in Admin → Payment Settings, they override .env so the dashboard is the source of truth.
     */
    protected function ensureSelcomConfigFromSettings(): void
    {
        $settings = Setting::whereIn('key', [
            'selcom_vendor_id', 'selcom_api_key', 'selcom_api_secret', 'selcom_is_live',
        ])->pluck('value', 'key');

        $vendor = trim((string) ($settings->get('selcom_vendor_id') ?? ''));
        $key = trim((string) ($settings->get('selcom_api_key') ?? ''));
        $secret = trim((string) ($settings->get('selcom_api_secret') ?? ''));
        $isLive = in_array($settings->get('selcom_is_live'), ['1', 'true', 'yes'], true);

        // If store settings have all three credentials, use them (override .env)
        if ($vendor !== '' && $key !== '' && $secret !== '') {
            config([
                'selcom.vendor' => $vendor,
                'selcom.key' => $key,
                'selcom.secret' => $secret,
                'selcom.live' => $isLive,
            ]);
            return;
        }

        // Otherwise fill only missing values from settings (env wins for any that are set)
        $cfgVendor = config('selcom.vendor');
        $cfgKey = config('selcom.key');
        $cfgSecret = config('selcom.secret');
        if (($cfgVendor === null || $cfgVendor === '') && $vendor !== '') {
            config(['selcom.vendor' => $vendor]);
        }
        if (($cfgKey === null || $cfgKey === '') && $key !== '') {
            config(['selcom.key' => $key]);
        }
        if (($cfgSecret === null || $cfgSecret === '') && $secret !== '') {
            config(['selcom.secret' => $secret]);
        }
    }

    public function pay(Order $order)
    {
        $this->ensureSelcomConfigFromSettings();

        $paymentPhone = session('payment_phone');

        // Validate payment phone
        if (!$paymentPhone) {
            Log::warning('Selcom payment attempt without phone number', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);
            return redirect()->route('checkout.create')->with('error', 'Payment phone number is missing. Please provide a valid phone number.');
        }

        // Validate phone format (Tanzanian format: 7XXXXXXXX or 255XXXXXXXXX)
        $cleanPhone = preg_replace('/[^0-9]/', '', $paymentPhone);
        if (!preg_match('/^(255)?[67]\d{8}$/', $cleanPhone)) {
            Log::warning('Invalid phone number format for Selcom payment', [
                'order_id' => $order->id,
                'phone' => $paymentPhone,
            ]);
            return redirect()->route('checkout.create')->with('error', 'Invalid phone number format. Please use format: 7XXXXXXXX');
        }

        // Normalize phone to 255XXXXXXXXX format
        if (strlen($cleanPhone) === 9) {
            $cleanPhone = '255' . $cleanPhone;
        }

        try {
            // Unique transaction ID for this attempt
            $transid = 'ORDER_' . $order->id . '_' . time();

            $data = [
                'name'           => $order->user->name,
                'email'          => $order->user->email,
                'phone'          => $cleanPhone,
                'amount'         => $order->total_price ?? $order->total,
                'transaction_id' => $transid,
                'no_redirection' => true,
                'currency'       => 'TZS',
                'items'          => $order->items->count(),
                'payment_phone'  => $cleanPhone,
            ];

            Log::info('Initiating Selcom Checkout', [
                'order_id' => $order->id,
                'transaction_id' => $transid,
                'amount' => $data['amount'],
                'phone' => substr($cleanPhone, -4), // Only log last 4 digits for privacy
                'user_id' => $order->user_id,
            ]);

            $checkout = Selcom::checkout($data);

            Log::info('Selcom Checkout Response Received', [
                'order_id' => $order->id,
                'transaction_id' => $transid,
                'response' => (array)$checkout,
            ]);

            $response = (array)$checkout;

            // Check for Selcom API errors
            if (isset($response['resultcode']) && $response['resultcode'] !== '000') {
                $errorMessage = $response['result'] ?? 'Payment gateway error';
                $errorCode = $response['resultcode'] ?? 'UNKNOWN';
                
                Log::error('Selcom API Error', [
                    'order_id' => $order->id,
                    'transaction_id' => $transid,
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'full_response' => $response,
                ]);

                return redirect()->route('checkout.create')->with('error', 'Payment gateway error: ' . $errorMessage . '. Please try again or contact support.');
            }

            if (isset($response['transid'])) {
                try {
                    Selcompay::create([
                        'transid' => $response['transid'],
                        'order_id' => $response['order_id'] ?? null,
                        'phone_number' => $cleanPhone,
                        'amount' => $data['amount'],
                        'payment_status' => 'pending',
                        'local_order_id' => $order->id,
                    ]);

                    Log::info('Selcom payment record created', [
                        'order_id' => $order->id,
                        'transid' => $response['transid'],
                        'selcom_order_id' => $response['order_id'] ?? null,
                    ]);

                    // Store payment phone in session for display
                    session(['payment_phone' => $cleanPhone]);

                    return view('checkout.payment-processing', compact('order'));
                    
                } catch (\Exception $dbError) {
                    Log::error('Failed to save Selcom payment record', [
                        'order_id' => $order->id,
                        'error' => $dbError->getMessage(),
                        'trace' => $dbError->getTraceAsString(),
                    ]);
                    
                    return redirect()->route('checkout.create')->with('error', 'An error occurred saving payment details. Please try again.');
                }
            } else {
                Log::error('Selcom Checkout Failed: No transid in response', [
                    'order_id' => $order->id,
                    'transaction_id' => $transid,
                    'response' => $response,
                ]);
                
                return redirect()->route('checkout.create')->with('error', 'Payment initiation failed. No transaction ID received from payment gateway.');
            }

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('Selcom Connection Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'type' => 'connection_timeout',
            ]);
            
            return redirect()->route('checkout.create')->with('error', 'Connection Error: ' . $e->getMessage());
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'N/A';
            
            Log::error('Selcom Request Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'type' => 'request_error',
            ]);
            
            return redirect()->route('checkout.create')->with('error', 'Payment gateway request failed: ' . $e->getMessage());
            
        } catch (\Exception $e) {
            Log::error('Selcom Payment Unexpected Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('checkout.create')->with('error', 'Payment Error: ' . $e->getMessage());
        }
    }

    public function checkStatus(Order $order)
    {
        $this->ensureSelcomConfigFromSettings();

        try {
            $selcompay = Selcompay::where('local_order_id', $order->id)->latest()->first();

            if (!$selcompay) {
                Log::warning('No Selcom payment record found for order', [
                    'order_id' => $order->id,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'No payment record found for this order.'
                ]);
            }

            if (!$selcompay->order_id) {
                // Check if payment has been pending too long (e.g., 10 minutes)
                $createdAt = \Carbon\Carbon::parse($selcompay->created_at);
                $minutesPending = $createdAt->diffInMinutes(now());
                
                if ($minutesPending > 10) {
                    Log::warning('Selcom payment timeout - no order_id after 10 minutes', [
                        'order_id' => $order->id,
                        'selcompay_id' => $selcompay->id,
                        'minutes_pending' => $minutesPending,
                    ]);
                    
                    $selcompay->update(['payment_status' => 'timeout']);
                    $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
                    
                    return response()->json([
                        'status' => 'timeout',
                        'message' => 'Payment request timed out. Please try again.'
                    ]);
                }
                
                Log::debug('Selcom payment still waiting for order_id', [
                    'order_id' => $order->id,
                    'minutes_pending' => $minutesPending,
                ]);
                
                return response()->json([
                    'status' => 'pending',
                    'message' => 'Waiting for payment confirmation...'
                ]);
            }

            // Query Selcom for payment status
            $status = Selcom::orderStatus($selcompay->order_id);
            $statusArr = (array)$status;

            Log::info('Selcom status check', [
                'order_id' => $order->id,
                'selcom_order_id' => $selcompay->order_id,
                'status_response' => $statusArr,
            ]);

            // Check if request was successful
            if (!isset($statusArr['resultcode'])) {
                Log::error('Invalid Selcom status response - no resultcode', [
                    'order_id' => $order->id,
                    'response' => $statusArr,
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unable to verify payment status. Please contact support.'
                ]);
            }

            // Handle non-successful result codes
            if ($statusArr['resultcode'] !== '000') {
                $errorMessage = $statusArr['result'] ?? 'Unknown error';
                
                Log::error('Selcom status check failed', [
                    'order_id' => $order->id,
                    'error_code' => $statusArr['resultcode'],
                    'error_message' => $errorMessage,
                    'response' => $statusArr,
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment verification failed: ' . $errorMessage
                ]);
            }

            // Check payment status in the data array
            if (isset($statusArr['data'][0]['payment_status'])) {
                $paymentStatus = $statusArr['data'][0]['payment_status'];
                
                if ($paymentStatus === 'COMPLETED') {
                    // Payment successful
                    $selcompay->update(['payment_status' => 'completed']);
                    $order->update(['payment_status' => 'paid', 'status' => 'processed']);

                    // When dealer pays with Selcom, create distribution sales with status complete
                    $order->load(['items.product.category', 'user']);
                    if ($order->user && $order->user->role === 'dealer') {
                        app(DistributionSaleService::class)->createFromOrder($order, 'complete');
                    }

                    // Clear the user's cart after successful payment
                    $cart = \App\Models\Cart::where('user_id', $order->user_id)->first();
                    if ($cart) {
                        // Decrement stock for each item
                        foreach ($order->items as $orderItem) {
                            $product = $orderItem->product;
                            if ($product && $product->stock_quantity >= $orderItem->quantity) {
                                $product->decrement('stock_quantity', $orderItem->quantity);
                            }
                        }
                        
                        // Clear cart
                        $cart->items()->delete();
                        $cart->delete();
                        
                        Log::info('Cart cleared after successful Selcom payment', [
                            'order_id' => $order->id,
                            'user_id' => $order->user_id,
                        ]);
                    }

                    Log::info('Selcom payment completed successfully', [
                        'order_id' => $order->id,
                        'selcom_order_id' => $selcompay->order_id,
                        'amount' => $selcompay->amount,
                    ]);

                    return response()->json([
                        'status' => 'completed',
                        'message' => 'Payment successful!'
                    ]);
                    
                } elseif (in_array($paymentStatus, ['FAILED', 'CANCELLED', 'EXPIRED'])) {
                    // Payment failed - mark order as cancelled
                    $selcompay->update(['payment_status' => 'failed']);
                    $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);

                    Log::warning('Selcom payment failed', [
                        'order_id' => $order->id,
                        'selcom_order_id' => $selcompay->order_id,
                        'payment_status' => $paymentStatus,
                    ]);

                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Payment ' . strtolower($paymentStatus) . '. Please try again.'
                    ]);
                    
                } else {
                    // Still pending
                    Log::debug('Selcom payment still pending', [
                        'order_id' => $order->id,
                        'payment_status' => $paymentStatus,
                    ]);
                    
                    return response()->json([
                        'status' => 'pending',
                        'message' => 'Payment is being processed...'
                    ]);
                }
            }

            // No payment status in response
            Log::warning('No payment status in Selcom response', [
                'order_id' => $order->id,
                'response' => $statusArr,
            ]);
            
            return response()->json([
                'status' => 'pending',
                'message' => 'Checking payment status...'
            ]);

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('Selcom status check connection error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to connect to payment gateway. Please check your connection.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Selcom status check error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error checking payment status: ' . $e->getMessage()
            ]);
        }
    }
}
