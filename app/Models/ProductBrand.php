<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBrand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'remote_id',
        'name_url',
        'description',
        'status',
    ];

    protected $hidden = [

        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_brand_id');
    }
}
