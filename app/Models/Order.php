<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_serial_no',
        'user_id',
        'subtotal',
        'tax',
        'discount',
        'shipping_charge',
        'cashier_id',
        'total',
        'order_type',
        'special_instructions',
        'payment_method',
        'payment_status',
        'status',
        'active',
        'reason',
        'refund_status',
        'source',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Payment record for this order.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function refunds()
    {
        return $this->hasMany(OrderRefund::class);
    }

    public function refundItems()
    {
        return $this->hasManyThrough(
            OrderRefundItem::class,
            OrderRefund::class,
            'order_id',   // FK on order_refunds
            'refund_id',  // FK on order_refund_items
            'id',         // PK on orders
            'id'          // PK on order_refunds
        );
    }
}
