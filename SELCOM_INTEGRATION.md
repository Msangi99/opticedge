# Selcom Payment Gateway Integration

## Overview
This e-commerce platform integrates with Selcom payment gateway to accept mobile money payments (M-Pesa, Tigo Pesa, Airtel Money, and HaloPesa) in Tanzania.

## Features
✅ **Comprehensive Error Handling**: All errors are logged with context and shown to users in a friendly way  
✅ **Phone Number Validation**: Validates Tanzanian phone numbers before initiating payment  
✅ **Payment Status Tracking**: Real-time polling of payment status  
✅ **Timeout Management**: Automatically handles payment timeouts (10 minutes)  
✅ **Failed Payment Handling**: Detects and handles failed, cancelled, and expired payments  
✅ **Detailed Logging**: All payment flows are logged for debugging  

## Configuration

### 1. Install Selcom Package
```bash
composer require bryceandy/laravel-selcom
```

### 2. Environment Variables
Update your `.env` file with Selcom credentials:

```env
# Selcom Payment Gateway Configuration
SELCOM_VENDOR_ID=your_vendor_id_here
SELCOM_API_KEY=your_api_key_here
SELCOM_API_SECRET=your_api_secret_here
SELCOM_IS_LIVE=false  # Set to true for production
SELCOM_PREFIX=selcom
SELCOM_REDIRECT_URL="${APP_URL}/checkout/redirect"
SELCOM_CANCEL_URL="${APP_URL}/checkout/cancel"
SELCOM_HEADER_COLOR=#fa8900
SELCOM_LINK_COLOR=#fa8900
SELCOM_BUTTON_COLOR=#fa8900
SELCOM_PAYMENT_EXPIRY=60
```

**To get Selcom credentials:**
1. Visit [Selcom Developer Portal](https://developers.selcommobile.com/)
2. Register or log in to your account
3. Create a new application
4. Copy the Vendor ID, API Key, and API Secret

### 3. Database Migration
Create the `selcompays` table:

```bash
php artisan make:migration create_selcompays_table
```

Migration should include:
```php
Schema::create('selcompays', function (Blueprint $table) {
    $table->id();
    $table->string('transid')->unique();
    $table->string('order_id')->nullable();
    $table->string('phone_number');
    $table->decimal('amount', 10, 2);
    $table->string('payment_status')->default('pending'); // pending, completed, failed, timeout
    $table->unsignedBigInteger('local_order_id');
    $table->timestamps();
    
    $table->foreign('local_order_id')->references('id')->on('orders')->onDelete('cascade');
});
```

Run the migration:
```bash
php artisan migrate
```

## Payment Flow

### 1. User Checkout
- User adds items to cart
- Goes to checkout page
- Selects Selcom as payment method
- Enters phone number (format: 7XXXXXXXX)

### 2. Payment Initiation
When user submits the order:
1. **Validation**: Phone number is validated (must be Tanzanian format)
2. **Order Creation**: Order is created in the database
3. **Selcom Request**: Initiate Selcom checkout
4. **Database Record**: Create payment record in `selcompays` table
5. **Redirect**: User is redirected to payment processing page

### 3. Payment Processing
The payment-processing page:
- Shows pending status with phone number hint
- Polls the server every 3 seconds to check payment status
- Displays appropriate messages based on payment state
- Timeout after 6 minutes (120 polls × 3 seconds)

### 4. Payment Status Check
The backend checks Selcom API for payment status:
- **COMPLETED**: Payment successful → Order marked as `paid`
- **FAILED/CANCELLED/EXPIRED**: Payment failed → Order marked as `cancelled`
- **PENDING**: Still processing → Continue polling
- **TIMEOUT**: No response after 10 minutes → Payment marked as `timeout`

### 5. Final Outcome
- **Success**: User redirected to orders page with success message
- **Failure**: Error displayed with "Try Again" button

## Error Handling

### Error Types

#### 1. Validation Errors
- **Missing Phone Number**: Redirects to checkout with error message
- **Invalid Phone Format**: Validates against Tanzanian format

#### 2. Connection Errors
- **Network Timeout**: Logged and user-friendly message shown
- **Connection Failed**: Detected and handled gracefully

#### 3. Selcom API Errors
- **API Error Codes**: Captured and logged with full context
- **Missing Response Data**: Detected and logged

#### 4. Payment Status Errors
- **Failed Payments**: Detected and order marked as cancelled
- **Timeout**: After 10 minutes, payment marked as timeout
- **Invalid Status**: Logged for investigation

### Error Logging

All errors are logged to Laravel logs (`storage/logs/laravel.log`) with context:

```php
// Example log entry
[2026-01-22 22:00:00] local.ERROR: Selcom API Error 
{
    "order_id": 123,
    "transaction_id": "ORDER_123_1737582000",
    "error_code": "001",
    "error_message": "Insufficient funds",
    "full_response": {...}
}
```

### Viewing Logs

To monitor payment errors in real-time:
```bash
# Windows (PowerShell)
Get-Content storage/logs/laravel.log -Tail 50 -Wait

# Linux/Mac
tail -f storage/logs/laravel.log
```

Filter for Selcom errors:
```bash
# Search for Selcom-related logs
Select-String -Path storage/logs/laravel.log -Pattern "Selcom"
```

## User-Facing Error Messages

| Error Type | User Message |
|-----------|--------------|
| Missing Phone | "Payment phone number is missing. Please provide a valid phone number." |
| Invalid Format | "Invalid phone number format. Please use format: 7XXXXXXXX" |
| Connection Error | "Unable to connect to payment gateway. Please check your internet connection and try again." |
| API Error | "Payment gateway error: [error message]. Please try again or contact support." |
| Payment Failed | "Payment [status]. Please try again." |
| Timeout | "Payment request timed out. Please try again." |
| Generic Error | "An unexpected error occurred processing the payment. Please try again or contact support." |

## Testing

### Test Mode
When `SELCOM_IS_LIVE=false`, you're in test mode. Use Selcom's test credentials.

### Test Scenarios

1. **Successful Payment**
   - Use test phone number provided by Selcom
   - Complete payment within the allocation time

2. **Failed Payment**
   - Cancel the payment request on your phone
   - Let it timeout

3. **Invalid Phone Number**
   - Try with invalid format
   - Should be rejected before calling Selcom API

### Debug Mode
Enable debug logging by setting in `.env`:
```env
LOG_LEVEL=debug
```

## Troubleshooting

### Common Issues

#### "Payment phone number is missing"
**Cause**: Phone number not stored in session  
**Solution**: Ensure phone number is submitted with checkout form

#### "Invalid phone number format"
**Cause**: Phone number doesn't match Tanzanian format  
**Solution**: Must be 9 digits starting with 6 or 7 (e.g., 712345678)

#### "Unable to connect to payment gateway"
**Cause**: Network issue or Selcom API is down  
**Solution**: Check internet connection, verify Selcom API status

#### "No transaction ID received"
**Cause**: Selcom API didn't return transid  
**Solution**: Check Selcom credentials, review logs for full response

#### Payment stuck in pending
**Cause**: Selcom hasn't sent webhook or status not updating  
**Solution**: Will timeout after 10 minutes, user can retry

## Security Considerations

1. **API Credentials**: Never commit real credentials to version control
2. **Phone Number Privacy**: Only last 4 digits logged
3. **HTTPS Required**: Use HTTPS in production for secure communication
4. **Input Validation**: All inputs validated before processing
5. **Session Security**: Payment phone stored securely in session

## Production Checklist

Before going live:
- [ ] Update `SELCOM_IS_LIVE=true`
- [ ] Use production Selcom credentials
- [ ] Set up HTTPS
- [ ] Test with real phone number (small amount)
- [ ] Set up log monitoring
- [ ] Configure log rotation
- [ ] Set up error alerting
- [ ] Test timeout scenarios
- [ ] Verify webhook handling (if applicable)

## Support

For Selcom API issues:
- Documentation: https://developers.selcommobile.com/
- Support: support@selcommobile.com

For integration issues:
- Review logs in `storage/logs/laravel.log`
- Check error messages on checkout page
- Verify Selcom credentials and configuration
