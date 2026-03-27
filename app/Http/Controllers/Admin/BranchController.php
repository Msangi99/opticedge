<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount('purchases')->orderBy('name')->get();

        $branchDashboard = [
            'branches' => $branches->count(),
            'linked_purchases' => (int) $branches->sum('purchases_count'),
        ];

        return view('admin.branches.index', compact('branches', 'branchDashboard'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Branch::create($validated);

        return redirect()->route('admin.branches.index')->with('success', 'Branch created.');
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $branch->update($validated);

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->purchases()->exists()) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Cannot delete a branch that has purchases. Reassign purchases first.');
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted.');
    }
}
