<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * List categories for admin add-product dropdown.
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return response()->json(['data' => $categories]);
    }
}
