<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings_shipping_costs extends Model
{
    protected $fillable = [
        'min_value_threshold',
        'max_value_threshold',
        'shipping_cost'
    ];

    protected $table = 'settings_shipping_costs';
}
