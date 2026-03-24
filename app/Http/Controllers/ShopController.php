<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $productsQuery = Product::query();

        $filterByCategory = $request->get('category');
        if ($filterByCategory && is_array($filterByCategory)) {
            $productsQuery->whereIntegerInRaw('products.category_id', $filterByCategory);
        } else $filterByCategory = null;

        $filterByPrice = $request->get('price');
        if ($filterByPrice && is_array($filterByPrice)) {
            $productsQuery->whereBetween('products.price', [$filterByPrice['min'] ?? 0, $filterByPrice['max'] ?? 2**31-1]);
            if (isset($filterByPrice['discount'])) {
                $productsQuery->where('products.discount', '>', 0)
                    ->whereNotNull('products.discount_min_qty');
            }
        } else $filterByPrice = null;

        $search = $request->get('search');
        if ($search) {
        $productsQuery->where('products.name', 'like', "%$search%");
        }

        $orderByElement = $request->get('order-element');
        $orderByDirection = $request->get('order-direction') ?? 'desc';
        if ($orderByElement) {
            if ($orderByElement === 'category') {
                $productsQuery->join('categories', 'products.category_id', '=', 'categories.id')
                    ->orderBy('categories.name', $orderByDirection);
            } else {
                $productsQuery->orderBy($orderByElement, $orderByDirection)
                    ->orderBy('products.created_at', $orderByDirection);
            }
        } else {
            $productsQuery->orderBy('products.created_at', $orderByDirection);
        }

        try {
            $products = $productsQuery
                ->with('category')
                ->paginate(50)
                ->withQueryString();
        } catch (\Exception $error) {
            $products = new LengthAwarePaginator([], 0, 1);
        }

        return view('shop.index')
            ->with('products', $products)
            ->with(compact('filterByCategory', 'filterByPrice', 'search', 'orderByElement', 'orderByDirection'));
    }
}