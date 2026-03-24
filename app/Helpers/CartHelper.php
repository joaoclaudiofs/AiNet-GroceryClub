<?php

namespace App\Helpers;

class CartHelper
{
    public static function getDiscountedPrice($product, $quantity)
    {

    }

    public static function getSubtotal($product, $quantity)
    {
        return self::getDiscountedPrice($product, $quantity) * $quantity;
    }

    public static function isOutOfStock($product)
    {
        return $product->stock <= 0;
    }

    public static function isLowStock($product)
    {
        return $product->stock > 0 && $product->stock < 5;
    }
}
