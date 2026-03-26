<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRefundItem extends Model
{
    protected $fillable = [
        'refund_id',
        'product_id',
        'quantity',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Item belongs to refund
    public function refund()
    {
        return $this->belongsTo(OrderRefund::class, 'refund_id');
    }

    // Item belongs to product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
