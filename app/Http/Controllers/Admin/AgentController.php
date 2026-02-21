<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentAssignment;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    public function index()
    {
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        return view('admin.agents.index', compact('agents'));
    }

    public function show(User $agent)
    {
        if ($agent->role !== 'agent') {
            abort(404);
        }
        $assignments = AgentAssignment::where('agent_id', $agent->id)->with('product.category')->get();
        return view('admin.agents.show', compact('agent', 'assignments'));
    }

    public function assignProductsForm()
    {
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        $products = Product::whereHas('purchases')->orderBy('name')->get();
        return view('admin.agents.assign-products', compact('agents', 'products'));
    }

    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity_assigned' => 'required|integer|min:1',
        ]);

        $user = User::findOrFail($validated['agent_id']);
        if ($user->role !== 'agent') {
            return back()->with('error', 'Selected user is not an agent.');
        }

        $assignment = AgentAssignment::firstOrNew([
            'agent_id' => $validated['agent_id'],
            'product_id' => $validated['product_id'],
        ]);
        $assignment->quantity_assigned = ($assignment->quantity_assigned ?? 0) + $validated['quantity_assigned'];
        $assignment->save();

        return redirect()->route('admin.agents.assign-products')->with('success', 'Products assigned to agent.');
    }

    public function create()
    {
        return view('admin.agents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'agent';
        $validated['status'] = 'active';
        $user = User::create($validated);
        $user->forceFill(['email_verified_at' => now()])->save();
        return redirect()->route('admin.agents.index')->with('success', 'Agent created. They can log in and will see their dashboard.');
    }
}
