<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentAssignment;
use App\Models\AgentProductListAssignment;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductListItem;
use App\Models\SubadminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'product_id' => 'required|exists:products,id',
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

    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'product_list_ids' => 'required|array|min:1',
            'product_list_ids.*' => 'distinct|integer|exists:product_list,id',
        ]);

        $user = User::findOrFail($validated['agent_id']);
        if ($user->role !== 'agent') {
            return back()->with('error', 'Selected user is not an agent.');
        }

        $productId = (int) $validated['product_id'];
        $ids = array_unique(array_map('intval', $validated['product_list_ids']));

        try {
            DB::transaction(function () use ($user, $productId, $ids) {
                $hasUnsoldAssigned = AgentProductListAssignment::query()
                    ->where('agent_id', $user->id)
                    ->whereHas('productListItem', fn ($q) => $q->whereNull('sold_at'))
                    ->exists();

                if ($hasUnsoldAssigned) {
                    throw new \InvalidArgumentException(
                        'This agent still has assigned device(s) that are not sold yet. Sell or unassign them before assigning more stock.'
                    );
                }

                $added = 0;

                foreach ($ids as $listId) {
                    $item = ProductListItem::lockForUpdate()->find($listId);

                    if (! $item || ! $item->isCatalogProduct($productId)) {
                        throw new \InvalidArgumentException('One or more IMEIs do not belong to the selected product.');
                    }

                    if ($item->isSold()) {
                        throw new \InvalidArgumentException('One or more devices are already sold.');
                    }

                    if (! $item->isPurchasePaid()) {
                        throw new \InvalidArgumentException('One or more devices are not from an eligible purchase (paid, partial, unpaid, or purchase still has IMEI limit remaining).');
                    }

                    if ($item->agentProductListAssignment) {
                        throw new \InvalidArgumentException('One or more devices are already assigned to an agent.');
                    }

                    AgentProductListAssignment::create([
                        'agent_id' => $user->id,
                        'product_list_id' => $item->id,
                    ]);
                    $added++;
                }

                if ($added === 0) {
                    throw new \InvalidArgumentException('No devices were assigned.');
                }

                $assignment = AgentAssignment::firstOrNew([
                    'agent_id' => $user->id,
                    'product_id' => $productId,
                ]);
                $assignment->quantity_assigned = (int) ($assignment->quantity_assigned ?? 0) + $added;
                $assignment->save();
            });
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
}
