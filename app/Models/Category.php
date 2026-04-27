<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Category extends Model
{
    protected static ?string $resolvedTable = null;

    protected $fillable = ['name', 'image'];

    public function getTable()
    {
        if (static::$resolvedTable !== null) {
            return static::$resolvedTable;
        }

        // Support both legacy schema (`categories`) and renamed schema (`brands`).
        static::$resolvedTable = Schema::hasTable('brands') ? 'brands' : 'categories';

        return static::$resolvedTable;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
