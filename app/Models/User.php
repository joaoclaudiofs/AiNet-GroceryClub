<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'blocked',
        'nif',
        'gender',
        'photo',
        'default_delivery_address',
        'default_payment_type',
        'default_payment_reference'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function card()
    {
        return $this->hasOne(Card::class, 'id', 'id')->withTrashed();
    }

    public function supply_orders()
    {
        return $this->hasMany(Supply_order::class, 'registered_by_user_id', 'id');
    }

    public function stock_adjustments()
    {
        return $this->hasMany(Stock_adjustment::class, 'registered_by_user_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'member_id', 'id');
    }

    public function firstLastInitial(): string
    {
        $allNames = Str::of($this->name)
            ->explode(' ');
        $firstName = $allNames->first();
        $lastName = $allNames->count() > 1 ? $allNames->last() : '';
        return Str::of($firstName)->substr(0, 1)
            ->append(' ')
            ->append(Str::of($lastName)->substr(0, 1));
    }

    public function firstLastName(): string
    {
        $allNames = Str::of($this->name)
            ->explode(' ');
        $firstName = $allNames->first();
        $lastName = $allNames->count() > 1 ? $allNames->last() : '';
        return Str::of($firstName)
            ->append(' ')
            ->append(Str::of($lastName));
    }

    public function getPhotoUrlAttribute()
    {

        if ($this->photo && !Str::endsWith($this->photo, '.Identifier')
            && Storage::disk('public')->exists("users/{$this->photo}")) {
            return asset("storage/users/{$this->photo}");
        } else {
            return asset("storage/users/anonymous.png");
        }
    }
}
