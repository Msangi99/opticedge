<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentAssignment;
use App\Models\AgentProductListAssignment;
use App\Models\Product;
use App\Models\ProductListItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * JSON for Select2: unsold, paid purchase, not yet assigned to any agent.
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

                    if (! $item || (int) $item->product_id !== $productId) {
                        throw new \InvalidArgumentException('One or more IMEIs do not belong to the selected product.');
                    }

                    if ($item->isSold()) {
                        throw new \InvalidArgumentException('One or more devices are already sold.');
                    }

                    if (! $item->isPurchasePaid()) {
                        throw new \InvalidArgumentException('One or more devices are not from a paid purchase.');
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
