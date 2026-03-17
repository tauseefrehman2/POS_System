<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total',
        'product_name',
        'slug',
        'sku',
        'product_category_id',
        'product_brand_id',
        'buying_price',
        'selling_price',
        'variation_price',
        'status',
        'order',
        'can_purchasable',
        'show_stock_out',
        'maximum_purchase_quantity',
        'low_stock_quantity_warning',
        'weight',
        'refundable',
        'description',
        'shipping_and_return',
        'add_to_flash_sale',
        'discount',
        'offer_start_date',
        'offer_end_date',
        'shipping_type',
        'shipping_cost',
        'is_product_quantity_multiply',
        'stock_quantity',
        'barcode',
    ];

    protected $casts = [
        'price' => 'decimal:6',
        'total' => 'decimal:6',
        'buying_price' => 'decimal:6',
        'selling_price' => 'decimal:6',
        'variation_price' => 'decimal:6',
        'discount' => 'decimal:6',
        'shipping_cost' => 'decimal:6',
        'offer_start_date' => 'datetime',
        'offer_end_date' => 'datetime',
        'status' => 'boolean',
        'can_purchasable' => 'boolean',
        'show_stock_out' => 'boolean',
        'refundable' => 'boolean',
        'add_to_flash_sale' => 'boolean',
        'is_product_quantity_multiply' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
