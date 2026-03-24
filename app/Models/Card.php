<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'card_number',
        'balance'
    ];

    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id')->withTrashed();
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'card_id', 'id');
    }
}
