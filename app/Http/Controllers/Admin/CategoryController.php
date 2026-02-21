<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::withCount(['products' => function ($query) {
            $query->where('stock_quantity', '>', 0);
        }])->withSum('products', 'stock_quantity')
            ->with(['products' => fn ($q) => $q->select('id', 'category_id', 'name', 'stock_quantity')])
            ->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ]);

        // Check for server size limit drop
        if ($request->hasFile('image') === false && $request->header('Content-Length') > 2 * 1024 * 1024) {
             $maxSize = ini_get('upload_max_filesize');
             Log::error('Category creation failed: Image upload exceeded server limit.', [
                 'content_length' => $request->header('Content-Length'),
                 'max_allowed' => $maxSize,
                 'user_id' => auth()->id()
             ]);
             return back()->withInput()->withErrors(['image' => "The uploaded file exceeded the server upload limit of {$maxSize}."]);
        }

        $data = ['name' => $request->name];
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        return redirect()->route('admin.categories.index');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('image') === false && $request->header('Content-Length') > 2 * 1024 * 1024) {
             $maxSize = ini_get('upload_max_filesize');
             Log::error('Category update failed: Image upload exceeded server limit.', [
                 'category_id' => $category->id,
                 'content_length' => $request->header('Content-Length'),
                 'max_allowed' => $maxSize
             ]);
             return back()->withInput()->withErrors(['image' => "The uploaded file exceeded the server upload limit of {$maxSize}."]);
        }

        $data = ['name' => $request->name];
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Delete old image if exists? (Optional but good practice)
            // if ($category->image) { Storage::disk('public')->delete($category->image); }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
