<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPaymentHistory extends Model
{
    protected $fillable = [
        'date',
        'payment_name',
        'user_id',
        'credit',
        'debit',
    ];

    // relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
