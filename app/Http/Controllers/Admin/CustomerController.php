<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\QueryException;

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

    public function destroy(User $user)
    {
        if (($user->role ?? '') === 'admin') {
            return redirect()->route('admin.customers.index', request()->query())
                ->withErrors(['error' => 'Admin account cannot be deleted here.']);
        }

        if ((int) $user->id === (int) auth()->id()) {
            return redirect()->route('admin.customers.index', request()->query())
                ->withErrors(['error' => 'You cannot delete your own account.']);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            return redirect()->route('admin.customers.index', request()->query())
                ->withErrors(['error' => 'Cannot delete this user because it is linked to existing records.']);
        }

        return redirect()->route('admin.customers.index', request()->query())
            ->with('success', 'User deleted successfully.');
    }
}
