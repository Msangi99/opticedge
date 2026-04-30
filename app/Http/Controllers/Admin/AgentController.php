<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentAssignment;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductListItem;
use App\Models\SubadminRole;
use App\Models\User;
use App\Services\AgentProductAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class AgentController extends Controller
{
    public function index()
    {
        $agents = User::where('role', 'agent')->with('branch')->orderBy('name')->get();
        return view('admin.agents.index', compact('agents'));
    }

    public function subadminsIndex()
    {
        $subadmins = User::where('role', 'subadmin')->with('subadminRole')->orderBy('name')->get();
        return view('admin.subadmins.index', compact('subadmins'));
    }

    public function show(User $agent)
    {
        if ($agent->role !== 'agent') {
            abort(404);
        }
        $agent->load('branch');
        $assignments = AgentAssignment::where('agent_id', $agent->id)->with('product.category')->get();
        return view('admin.agents.show', compact('agent', 'assignments'));
    }

    public function assignProductsForm()
    {
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        $products = Product::whereHas('purchases')->orderBy('name')->get();

        return view('admin.agents.assign-products', compact('agents', 'products'));
    }

    /**
     * JSON for Select2: unsold, eligible purchase (paid / partial / unpaid or limit remaining), not yet assigned.
     */
    public function assignableImeis(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:models,id',
        ]);

        $items = ProductListItem::assignableToAgent((int) $validated['product_id'])
            ->orderBy('imei_number')
            ->get(['id', 'imei_number', 'model']);

        return response()->json([
            'data' => $items->map(fn ($i) => [
                'id' => $i->id,
                'text' => $i->imei_number . ($i->model ? ' – ' . $i->model : ''),
            ])->values()->all(),
        ]);
    }

    public function storeAssignment(Request $request, AgentProductAssignmentService $assignmentService)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:models,id',
            'product_list_ids' => 'required|array|min:1',
            'product_list_ids.*' => 'distinct|integer|exists:product_list,id',
        ]);

        $user = User::findOrFail($validated['agent_id']);

        try {
            $assignmentService->assignToAgent(
                $user,
                (int) $validated['product_id'],
                $validated['product_list_ids']
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.agents.assign-products')->with('success', 'Products assigned to agent.');
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();

        return view('admin.agents.create', compact('branches'));
    }

    public function createSubadmin()
    {
        $roles = SubadminRole::orderBy('name')->get();

        return view('admin.subadmins.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'agent';
        if (Schema::hasColumn('users', 'ability')) {
            $validated['ability'] = 'fullaccess';
        }
        $validated['status'] = 'active';
        if (empty($validated['branch_id'])) {
            $validated['branch_id'] = null;
        }
        $user = User::create($validated);
        $user->forceFill(['email_verified_at' => now()])->save();
        return redirect()->route('admin.agents.index')->with('success', 'Agent created. They can log in and will see their dashboard.');
    }

    public function storeSubadmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:100',
            'subadmin_role_id' => 'required|exists:subadmin_roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'subadmin';
        $validated['status'] = 'active';
        $validated['branch_id'] = null;

        $user = User::create($validated);
        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('admin.subadmins.index')->with('success', 'Leader created successfully.');
    }

    public function transferBranch(Request $request, User $agent)
    {
        if ($agent->role !== 'agent') {
            abort(404);
        }

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $agent->update(['branch_id' => $validated['branch_id'] ?? null]);

        return redirect()->route('admin.agents.index')->with('success', 'Agent transferred successfully.');
    }

    public function deactivate(User $user)
    {
        if (! in_array($user->role, ['agent', 'subadmin'], true)) {
            abort(404);
        }

        $user->update(['status' => 'inactive']);

        $targetRoute = $user->role === 'agent'
            ? 'admin.agents.index'
            : 'admin.subadmins.index';
        $label = $user->role === 'agent' ? 'Agent' : 'Leader';

        return redirect()->route($targetRoute)->with('success', $label . ' deactivated successfully.');
    }

    public function activate(User $user)
    {
        if (! in_array($user->role, ['agent', 'subadmin'], true)) {
            abort(404);
        }

        $user->update(['status' => 'active']);

        $targetRoute = $user->role === 'agent'
            ? 'admin.agents.index'
            : 'admin.subadmins.index';
        $label = $user->role === 'agent' ? 'Agent' : 'Leader';

        return redirect()->route($targetRoute)->with('success', $label . ' activated successfully.');
    }

    public function destroy(User $user)
    {
        if (! in_array($user->role, ['agent', 'subadmin'], true)) {
            abort(404);
        }

        if ((int) $user->id === (int) auth()->id()) {
            $targetRoute = $user->role === 'agent' ? 'admin.agents.index' : 'admin.subadmins.index';
            return redirect()->route($targetRoute)
                ->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $targetRoute = $user->role === 'agent' ? 'admin.agents.index' : 'admin.subadmins.index';
        $label = $user->role === 'agent' ? 'Agent' : 'Leader';

        try {
            $user->delete();
        } catch (QueryException $e) {
            return redirect()->route($targetRoute)
                ->withErrors(['error' => 'Cannot delete this ' . strtolower($label) . ' because it is linked to existing records.']);
        }

        return redirect()->route($targetRoute)->with('success', $label . ' deleted successfully.');
    }
}
