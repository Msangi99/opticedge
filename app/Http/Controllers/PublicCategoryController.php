<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class PublicCategoryController extends Controller
{
    public function show(Category $category)
    {
        return view('public.categories.show', compact('category'));
    }
}
