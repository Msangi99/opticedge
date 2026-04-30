<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $customers = $query->latest()->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    public function activate(User $user)
    {
        $user->update(['status' => 'active']);

        return redirect()->route('admin.customers.index', request()->query())
            ->with('success', 'User activated successfully.');
    }

    public function deactivate(User $user)
    {
        if (($user->role ?? '') === 'admin') {
            return redirect()->route('admin.customers.index', request()->query())
                ->withErrors(['error' => 'Admin account cannot be deactivated here.']);
        }

        $user->update(['status' => 'inactive']);

        return redirect()->route('admin.customers.index', request()->query())
            ->with('success', 'User deactivated successfully.');
    }
}
