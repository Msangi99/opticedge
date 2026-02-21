<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $url = auth()->user()->role === 'admin'
            ? route('admin.dashboard', absolute: false)
            : route('dashboard', absolute: false);

        $this->redirectIntended(default: $url, navigate: true);
    }
}; ?>

<div>
    <h2 class="text-2xl font-normal text-slate-900 mb-6">Sign in</h2>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div class="mb-4">
            <label for="email" class="block text-sm font-bold text-slate-900 mb-1">Email or mobile phone number</label>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus
                autocomplete="username"
                class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring focus:ring-[#fa8900] focus:ring-opacity-50 text-sm py-2">
            <x-input-error :messages="$errors->get('form.email')" class="mt-2 text-red-600 text-xs" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-bold text-slate-900">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                        class="text-xs text-blue-600 hover:text-[#fa8900] hover:underline">
                        Forgot your password?
                    </a>
                @endif
            </div>
            <input wire:model="form.password" id="password" type="password" name="password" required
                autocomplete="current-password"
                class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring focus:ring-[#fa8900] focus:ring-opacity-50 text-sm py-2">
            <x-input-error :messages="$errors->get('form.password')" class="mt-2 text-red-600 text-xs" />
        </div>

        <div class="mt-6">
            <button type="submit"
                class="w-full bg-[#fa8900] hover:bg-[#e87b00] text-white font-medium py-2 px-4 rounded-md shadow-sm transition-colors text-sm">
                Sign in
            </button>
        </div>

        <div class="mt-4 flex items-center">
            <input wire:model="form.remember" id="remember" type="checkbox"
                class="rounded border-gray-300 text-[#fa8900] shadow-sm focus:ring-[#fa8900]" name="remember">
            <label for="remember" class="ml-2 block text-sm text-slate-900">
                Keep me signed in.
            </label>
        </div>
    </form>

    <div class="mt-8 relative">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-slate-200"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="px-2 bg-white text-xs text-slate-500">New to OpticEdgeAfrica?</span>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('register') }}" wire:navigate
            class="w-full flex justify-center py-2 px-4 border border-slate-300 rounded-md shadow-sm bg-slate-50 text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors">
            Create your OpticEdgeAfrica account
        </a>
    </div>

    <div class="mt-4 text-center space-y-1">
        <a href="{{ route('dealer.register') }}" class="block text-xs text-slate-500 hover:text-[#fa8900] hover:underline">
            Want to become a seller? Register as a Dealer
        </a>
        <a href="{{ route('agent.register') }}" wire:navigate class="block text-xs text-slate-500 hover:text-[#fa8900] hover:underline">
            Register as an Agent
        </a>
    </div>
</div>