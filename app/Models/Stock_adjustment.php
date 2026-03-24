<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock_adjustment extends Model
{
    protected $fillable = [
        'product_id',
        'registered_by_user_id',
        'quantity_changed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'registered_by_user_id', 'id')->withTrashed();
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
