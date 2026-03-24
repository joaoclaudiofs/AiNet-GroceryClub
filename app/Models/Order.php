<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'member_id',
        'shipping_cost',
        'total',
        'nif',
        'delivery_address',
        'pdf_receipt',
        'cancel_reason',
        'date',
        'status',
        'total_items',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'member_id', 'id')->withTrashed();
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'order_id', 'id');
    }

    public function item_orders()
    {
        return $this->hasMany(Item_order::class, 'order_id', 'id');
    }

    /*
    public function member()
    {
        return $this->belongsTo(\App\Models\User::class, 'member_id');
    }

    public function item_orders()
    {
        return $this->hasMany(\App\Models\Item_order::class);
    }
    */

}
