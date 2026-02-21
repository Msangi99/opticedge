<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center" x-data="paymentPoller({{ $order->id }})">
        <!-- Loading State -->
        <div x-show="status === 'pending'" class="mb-4 flex justify-center">
            <svg class="h-24 w-24 text-[#fa8900] animate-pulse" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
        </div>

        <!-- Success State -->
        <div x-show="status === 'completed'" style="display:none;" class="mb-4 flex justify-center">
            <svg class="h-24 w-24 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <!-- Error/Failed State -->
        <div x-show="status === 'error' || status === 'failed' || status === 'timeout'" style="display:none;"
            class="mb-4 flex justify-center">
            <svg class="h-24 w-24 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <!-- Pending State -->
        <div x-show="status === 'pending'">
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Check Your
                {{ session('payment_phone') ? 'Phone ending in ' . substr(session('payment_phone'), -4) : 'Phone' }}
            </h2>
            <p class="text-lg text-slate-600 mb-8 max-w-lg mx-auto">
                We've sent a payment request. Please enter your PIN on your mobile phone to approve the transaction.
            </p>
            <div class="text-sm text-slate-500">
                <span x-text="message">Waiting for confirmation...</span>
            </div>
        </div>

        <!-- Success State -->
        <div x-show="status === 'completed'" style="display:none;">
            <h2 class="text-2xl font-bold text-green-600 mb-2">Payment Successful!</h2>
            <p class="text-lg text-slate-600 mb-8 max-w-lg mx-auto">
                Your payment has been processed successfully. Redirecting to your orders...
            </p>
        </div>

        <!-- Error State -->
        <div x-show="status === 'error' || status === 'failed' || status === 'timeout'" style="display:none;">
            <h2 class="text-2xl font-bold text-red-600 mb-2">
                <span x-show="status === 'timeout'">Payment Timeout</span>
                <span x-show="status === 'failed'">Payment Failed</span>
                <span x-show="status === 'error'">Payment Error</span>
            </h2>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 max-w-lg mx-auto">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium" x-text="message">
                            An error occurred processing your payment.
                        </p>
                    </div>
                </div>
            </div>
            <div class="space-y-3">
                <a href="{{ route('checkout.create') }}"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-[#fa8900] hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#fa8900]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Try Again
                </a>
                <div>
                    <a href="{{ route('orders.index') }}" class="text-sm text-slate-600 hover:text-slate-900 underline">
                        Go to My Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('paymentPoller', (orderId) => ({
                    status: 'pending',
                    message: 'Waiting for confirmation...',
                    pollCount: 0,
                    maxPolls: 120, // Poll for max 6 minutes (120 * 3 seconds)

                    init() {
                        this.poll();
                    },

                    poll() {
                        if (this.status !== 'pending') return;
                        if (this.pollCount >= this.maxPolls) {
                            this.status = 'timeout';
                            this.message = 'Payment request timed out. Please try again.';
                            return;
                        }

                        this.pollCount++;

                        fetch(`/checkout/status/${orderId}`)
                            .then(res => {
                                if (!res.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return res.json();
                            })
                            .then(data => {
                                console.log('Payment status:', data);

                                // Update message if provided
                                if (data.message) {
                                    this.message = data.message;
                                }

                                if (data.status === 'completed') {
                                    this.status = 'completed';
                                    this.message = data.message || 'Payment successful!';
                                    // Redirect after 2 seconds
                                    setTimeout(() => {
                                        window.location.href = "{{ route('orders.index') }}?success=Payment+Successful";
                                    }, 2000);

                                } else if (data.status === 'failed') {
                                    this.status = 'failed';
                                    this.message = data.message || 'Payment failed. Please try again.';

                                } else if (data.status === 'timeout') {
                                    this.status = 'timeout';
                                    this.message = data.message || 'Payment request timed out.';

                                } else if (data.status === 'error') {
                                    this.status = 'error';
                                    this.message = data.message || 'An error occurred. Please try again.';

                                } else {
                                    // Still pending, continue polling
                                    setTimeout(() => this.poll(), 3000);
                                }
                            })
                            .catch(error => {
                                console.error('Payment check failed:', error);
                                this.status = 'error';
                                this.message = 'Unable to check payment status. Please check your connection and try again.';
                            });
                    }
                }))
            })
        </script>
    @endpush
</x-app-layout>