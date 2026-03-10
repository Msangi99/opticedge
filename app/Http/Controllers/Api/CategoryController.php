<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List categories. For admin list (with_counts=1) includes products_count.
     */
    public function index(Request $request)
    {
        $withCounts = $request->query('with_counts');
        $query = Category::orderBy('name');

        if ($withCounts) {
            $categories = $query->withCount('products')->get(['id', 'name'])->map(function ($c) {
                return ['id' => $c->id, 'name' => $c->name, 'products_count' => $c->products_count ?? 0];
            });
        } else {
            $categories = $query->get(['id', 'name']);
        }

        return response()->json(['data' => $categories]);
    }
}
