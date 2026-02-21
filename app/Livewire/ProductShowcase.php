<?php

namespace App\Livewire;

use Livewire\Component;

class ProductShowcase extends Component
{
    public $categoryId = null;
    public $showCategories = true;

    public function mount($categoryId = null, $showCategories = true)
    {
        $this->categoryId = $categoryId;
        $this->showCategories = $showCategories;
    }

    public function render()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        
        $productsQuery = \App\Models\Product::query();
        
        if ($this->categoryId) {
            $productsQuery->where('category_id', $this->categoryId);
        }
        
        $products = $productsQuery->with('category')->latest()->take(12)->get();

        return view('livewire.product-showcase', [
            'categories' => $categories,
            'products' => $products
        ]);
    }
}
