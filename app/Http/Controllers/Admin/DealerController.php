<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DealerController extends Controller
{
    public function index()
    {
        $dealers = User::where('role', 'dealer')->orderBy('created_at', 'desc')->get();
        return view('admin.dealers.index', compact('dealers'));
    }

    public function create()
    {
        return view('admin.dealers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:100',
            'business_name' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'business_name' => $validated['business_name'],
            'role' => 'dealer',
            'status' => 'active',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('admin.dealers.index')->with('success', 'Dealer created. They can sign in with the email and password you set.');
    }

    public function approve(User $user)
    {
        if ($user->role !== 'dealer') {
            return back()->with('error', 'User is not a dealer.');
        }

        $user->update(['status' => 'active']);
        
        // In a real app, send an email here notifying the dealer.

        return back()->with('success', 'Dealer approved successfully.');
    }
    
    public function reject(User $user)
    {
        if ($user->role !== 'dealer') {
            return back()->with('error', 'User is not a dealer.');
        }

        $user->update(['status' => 'suspended']); // Or delete? Let's suspend for now.

        return back()->with('success', 'Dealer rejected/suspended.');
    }

    public function show(User $user)
    {
        if ($user->role !== 'dealer') {
            abort(404);
        }
        
        $user->load('addresses');

        return view('admin.dealers.show', compact('user'));
    }
}
