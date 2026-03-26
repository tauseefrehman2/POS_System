<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    protected $fillable = [
        'order_id',
        'reason',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Refund belongs to Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Refund has many items
    public function items()
    {
        return $this->hasMany(OrderRefundItem::class, 'refund_id');
    }
}
