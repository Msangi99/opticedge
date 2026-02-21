<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Registration Pending Approval') }}</h2>
        <p class="mb-4">
            {{ __('Thank you for registering as a dealer. Your account is currently under review by our administrators.') }}
        </p>
        <p>
            {{ __('You will be notified once your account is approved. Please check your email later or contact support if you have questions.') }}
        </p>
    </div>

    <div class="flex items-center justify-end mt-4">
        <a href="{{ route('welcome') }}" class="underline text-sm text-gray-600 hover:text-gray-900">
            {{ __('Return Home') }}
        </a>
    </div>
</x-guest-layout>