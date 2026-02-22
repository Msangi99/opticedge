<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ensure sanctum guard is defined (for API token auth from Flutter app)
        $guards = config('auth.guards', []);
        if (! isset($guards['sanctum'])) {
            config(['auth.guards.sanctum' => [
                'driver' => 'sanctum',
                'provider' => 'users',
            ]]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view) {
            $cartCount = 0;
            if (\Illuminate\Support\Facades\Auth::check()) {
                $cart = \App\Models\Cart::where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
                if ($cart) {
                    $cartCount = $cart->items()->sum('quantity');
                }
            }
            $view->with('cartCount', $cartCount);
        });
    }
}
