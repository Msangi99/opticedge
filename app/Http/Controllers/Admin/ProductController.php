<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = \App\Models\Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'rating' => 'required|numeric|min:0|max:5',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
            'images' => 'nullable|array|max:5',
        ]);

        // Check if upload failed due to server limits (e.g. upload_max_filesize = 2M)
        if ($request->hasFile('images') === false && $request->header('Content-Length') > 0) {
             $maxSize = ini_get('upload_max_filesize');
             // Rough estimation: if content length > 2MB and no files, it's likely dropped
             if ($request->header('Content-Length') > 2 * 1024 * 1024) { // > 2MB
                 Log::error('Product creation failed: Image upload exceeded server limit.', [
                     'content_length' => $request->header('Content-Length'),
                     'max_allowed' => $maxSize,
                     'user_id' => auth()->id()
                 ]);
                 return back()->withInput()->withErrors(['images' => "One or more files exceeded the server upload limit of {$maxSize}."]);
             }
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = $path;
                }
            }
        }

        Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'brand' => 'Samsung', // Enforce Samsung as per requirement
            'price' => $request->price,
            'rating' => $request->rating,
            'stock_quantity' => $request->stock_quantity,
            'description' => $request->description,
            'images' => $imagePaths,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Stock added successfully.');
    }
    /**
     * Show product list items (IMEI numbers) for this model.
     */
    public function showImei(Product $product)
    {
        $product->load(['productListItems' => function ($q) {
            $q->with('category:id,name')->orderBy('imei_number');
        }]);

        return view('admin.products.imei', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = \App\Models\Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'rating' => 'required|numeric|min:0|max:5',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'images' => 'nullable|array|max:5', // Max 5 files per upload
        ]);

        // Check if upload failed due to server limits
        if ($request->hasFile('images') === false && $request->header('Content-Length') > 2 * 1024 * 1024) {
            $maxSize = ini_get('upload_max_filesize');
            Log::error('Product update failed: Image upload exceeded server limit.', [
                'product_id' => $product->id,
                'content_length' => $request->header('Content-Length'),
                'max_allowed' => $maxSize
            ]);
            return back()->withInput()->withErrors(['images' => "One or more files exceeded the server upload limit of {$maxSize}."]);
        }

        // Logic: if new images are uploaded, determine strategy. 
        // For simplicity: Append new images to existing ones, up to 5.
        // If users want to delete, they would typically need a separate delete action or "replace all" logic.
        // Assuming "append" for now.
        
        $imagePaths = $product->images ? $product->images : [];
        
        // Ensure imagePaths is an array (handle potential casting issues if any)
        if (!is_array($imagePaths)) {
            $imagePaths = [];
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if (!$image->isValid()) continue;
                
                // Check if total images do not exceed 5
                if (count($imagePaths) >= 5) {
                    break;
                }
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
        }

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'rating' => $request->rating,
            'stock_quantity' => $request->stock_quantity,
            'description' => $request->description,
            'images' => $imagePaths,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Stock updated successfully.');
    }
}
