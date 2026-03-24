<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class View_product_stock_logs extends Model
{

    protected $table = 'view_product_stock_logs';


    protected $primaryKey = 'log_id';
    public $incrementing = false;
    protected $keyType = 'string';


    public $timestamps = false;

    protected $fillable = [
        'log_type',
        'log_id',
        'product_id',
        'registered_by_user_id',
        'quantity_changed',
        'status',
        'custom',
        'created_at',
        'updated_at',
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
