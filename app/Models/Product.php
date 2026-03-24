<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'category_id',
        'name',
        'price',
        'stock',
        'stock_lower_limit',
        'stock_upper_limit',
        'description',
        'photo',
        'discount',
        'discount_min_qty',
    ];

    public function getPhotoUrlAttribute()
    {
        if ($this->photo && Storage::disk('public')->exists("products/$this->photo")) {
            return asset("storage/products/$this->photo");
        } else {
            return asset("storage/products/product_no_image.png");
        }
    }

    public function getPhotoFullPathAttribute()
    {
        if ($this->photo && Storage::disk('public')->exists("products/$this->photo")) {
            return storage_path("app/public/products/{$this->photo}");
        } else {
            return storage_path("app/public/products/product_no_image.png");
        }
    }

    public function getPhotoEncode64Attribute() {
        $photoPath = $this->photo_full_path;
        $photoData = base64_encode(string: file_get_contents($photoPath));
        return 'data: ' . mime_content_type($photoPath) . ';base64,' . $photoData;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id')->withTrashed();
    }

    public function supply_orders()
    {
        return $this->hasMany(Supply_order::class, 'product_id', 'id');
    }

    public function stock_adjustments()
    {
        return $this->hasMany(Stock_adjustment::class, 'product_id', 'id');
    }

    public function item_orders()
    {
        return $this->hasMany(Item_order::class, 'product_id', 'id');
    }
}
