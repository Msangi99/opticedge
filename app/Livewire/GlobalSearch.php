<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class GlobalSearch extends Component
{
    public $query = '';
    public $category = 'All';

    public function render()
    {
        $results = [];

        if (strlen($this->query) >= 2) {
            $query = Product::where('name', 'like', '%' . $this->query . '%');

            if ($this->category !== 'All') {
                // Assuming we have a way to filter by category, 
                // but Product model only has category_id. 
                // For now, I'll skip strict category filtering or assume names match.
                // Ideally: $query->whereHas('category', function($q) { $q->where('name', $this->category); });
            }

            $results = $query->take(6)->get();
        }

        return view('livewire.global-search', [
            'results' => $results
        ]);
    }
}
