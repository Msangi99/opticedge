<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class DealerRegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register-dealer');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'dealer',
            'status' => 'pending', // Pending approval
        ]);

        event(new Registered($user));

        // We do NOT login the user. We redirect them to a page saying they need approval.
        // Or we login but they can't do anything. Better not to login to avoid complexity in middleware for now,
        // or login but redirect to a "Verify" page.
        // Let's redirect to a static page explaining the situation.

        return redirect()->route('dealer.pending');
    }

    public function pending()
    {
        return view('auth.dealer-pending');
    }
}
