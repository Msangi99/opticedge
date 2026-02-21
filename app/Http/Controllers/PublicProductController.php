<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    public function show(Product $product)
    {
        $relatedProducts = Product::where('id', '!=', $product->id)
            ->latest()
            ->take(5)
            ->get();

        return view('public.products.show', compact('product', 'relatedProducts'));
    }
}
