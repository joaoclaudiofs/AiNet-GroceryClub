<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockFormRequest;
use App\Models\Product;
use App\Models\Stock_adjustment;
use App\Models\View_product_stock_logs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ProductFormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Order;

class ProductController extends Controller
{



    public function index(Request $request): View
    {
        $productsQuery = Product::query();
        //$productsQuery->where('products.discount', '>', 0);

        $filterByCategory = $request->get('category');
        if ($filterByCategory && is_array($filterByCategory)) {
            $productsQuery->whereIntegerInRaw('products.category_id', $filterByCategory);
        } else $filterByCategory = null;

        $filterByPrice = $request->get('price');
        if ($filterByPrice && is_array($filterByPrice)) {
            $productsQuery->whereBetween('products.price', [$filterByPrice['min'] ?? 0, $filterByPrice['max'] ?? 2**31-1]);
        } else $filterByPrice = null;

        $filterByDiscount = $request->get('discount');
        if ($filterByDiscount && is_array($filterByDiscount) && (isset($filterByDiscount['min']) || isset($filterByDiscount['max']) || isset($filterByDiscount['no-null']))) {
            $productsQuery->whereBetween('products.discount', [$filterByDiscount['min'] ?? 0, $filterByDiscount['max'] ?? 2**8-1]);
            if (isset($filterByDiscount['no-null'])) {
                $productsQuery->whereNotNull('products.discount');
            } else {
                $productsQuery->orWhereNull('products.discount');
            }
        } else $filterByDiscount = null;

        $filterByDiscountMinQty = $request->get('discount_min_qty');
        if ($filterByDiscountMinQty && is_array($filterByDiscountMinQty) && (isset($filterByDiscountMinQty['min']) || isset($filterByDiscountMinQty['max']) || isset($filterByDiscountMinQty['no-null']))) {
            $productsQuery->whereBetween('products.discount_min_qty', [$filterByDiscountMinQty['min'] ?? 0, $filterByDiscountMinQty['max'] ?? 2**31-1]);
            if (isset($filterByDiscountMinQty['no-null'])) {
                $productsQuery->whereNotNull('products.discount_min_qty');
            } else {
                $productsQuery->orWhereNull('products.discount_min_qty');
            }
        } else $filterByDiscountMinQty = null;

        $filterByStock = $request->get('stock');
        if ($filterByStock && is_array($filterByStock)) {
            $productsQuery->whereBetween('products.stock', [$filterByStock['min'] ?? 0, $filterByStock['max'] ?? 2**31-1]);
        } else $filterByStock = null;

        $filterByStockLowerLimit = $request->get('stock_lower_limit');
        if ($filterByStockLowerLimit && is_array($filterByStockLowerLimit)) {
            $productsQuery->whereBetween('products.stock_lower_limit', [$filterByStockLowerLimit['min'] ?? 0, $filterByStockLowerLimit['max'] ?? 2**31-1]);
        } else $filterByStockLowerLimit = null;

        $filterByStockUpperLimit = $request->get('stock_upper_limit');
        if ($filterByStockUpperLimit && is_array($filterByStockUpperLimit)) {
            $productsQuery->whereBetween('products.stock_upper_limit', [$filterByStockUpperLimit['min'] ?? 0, $filterByStockUpperLimit['max'] ?? 2**31-1]);
        } else $filterByStockUpperLimit = null;

        $searchByName = $request->get('search-name');
        $searchWithRegex = $request->get('search-with-regex');
        if ($searchByName) {
            if ($searchWithRegex) {
                $productsQuery->where('products.name', 'regexp', $searchByName);
            } else {
                $productsQuery->where('products.name', 'like', "%$searchByName%");
            }
        }

        $orderByElement = $request->get('order-element');
        $orderByDirection = $request->get('order-direction') ?? 'asc';
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
                ->paginate(20)
                ->withQueryString();
        } catch (\Exception $error) {
            $products = new LengthAwarePaginator([], 0, 1);
        }

        return view('products.index')
            ->with('products', $products)
            ->with(compact('filterByCategory', 'filterByPrice', 'filterByDiscount', 'filterByDiscountMinQty', 'filterByStock', 'filterByStockLowerLimit', 'filterByStockUpperLimit', 'searchByName', 'searchWithRegex', 'orderByElement', 'orderByDirection'));
    }

    public function create(): View
    {
        $newProduct = new Product();
        return view('products.create')->with('product', $newProduct);
    }

    public function store(ProductFormRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        $newProduct = DB::transaction(function () use ($validatedData, $request) {
            $newProduct = new Product();
            $newProduct->name              = $validatedData['name'];
            $newProduct->category_id       = $validatedData['category'];
            $newProduct->price             = $validatedData['price'];
            $newProduct->stock             = $validatedData['stock'];
            $newProduct->stock_lower_limit = $validatedData['stock_lower_limit'];
            $newProduct->stock_upper_limit = $validatedData['stock_upper_limit'];
            $newProduct->description       = $validatedData['description'];
            $newProduct->discount          = $validatedData['discount'];
            $newProduct->discount_min_qty  = $validatedData['discount_min_qty'];
            $newProduct->save();

            $newStockAdjustment = new Stock_adjustment();
            $newStockAdjustment->product_id = $newProduct->id;
            $newStockAdjustment->quantity_changed = $validatedData['stock'];
            $newStockAdjustment->registered_by_user_id = auth()->id();
            $newStockAdjustment->save();

            $this->storeProductPhoto($request->photo_file, $newProduct);
            return $newProduct;
        });
        $url = route('products.show', ['product' => $newProduct]);
        $htmlMessage = "Product <a href='$url'><u>{$newProduct->name}</u></a> has been created successfully!";
        return redirect()->route('products.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', $htmlMessage);
    }

    private function storeProductPhoto(?UploadedFile $uploadedFile, Product $product): ?string
    {
        if ($uploadedFile) {
            $path = basename(Storage::disk('public')->putFile('products', $uploadedFile));
            $product->photo = $path;
            $product->save();
            return $path;
        }
        return null;
    }

    public function edit(Product $product): View
    {
        return view('products.edit')->with('product', $product);
    }

    public function update(ProductFormRequest $request, Product $product): RedirectResponse
    {
        $validatedData = $request->validated();
        $product = DB::transaction(function () use ($product, $validatedData, $request) {
            $newStockAdjustment = new Stock_adjustment();
            $newStockAdjustment->product_id = $product->id;
            $newStockAdjustment->quantity_changed = $validatedData['stock'] - $product->stock;
            $newStockAdjustment->registered_by_user_id = auth()->id();
            $newStockAdjustment->save();

            $product->name              = $validatedData['name'];
            $product->category_id       = $validatedData['category'];
            $product->price             = $validatedData['price'];
            $product->stock             = $validatedData['stock'];
            $product->stock_lower_limit = $validatedData['stock_lower_limit'];
            $product->stock_upper_limit = $validatedData['stock_upper_limit'];
            $product->description       = $validatedData['description'];
            $product->discount          = $validatedData['discount'];
            $product->discount_min_qty  = $validatedData['discount_min_qty'];
            $product->save();

            if ($request->photo_file) {
                $this->deleteProductPhoto($product);
                $this->storeProductPhoto($request->photo_file, $product);
            }
            return $product;
        });
        $url = route('products.show', ['product' => $product]);
        $htmlMessage = "Product <a href='$url'><u>{$product->name}</u></a> has been updated successfully!";
        return redirect()->route('products.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', $htmlMessage);
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $url = route('products.show', ['product' => $product]);
            DB::transaction(function () use ($product) {
                $fileName = $product->photo;
                $product->delete();
                $this->deletePhotoFile($fileName);
            });
            $alertType = 'success';
            $alertMsg = "Product <a href='$url'><u>{$product->name}</u></a> has been deleted successfully!";
        } catch (\Exception $error) {
            $alertType = 'danger';
            $alertMsg = "It was not possible to delete the product
                            <a href='$url'><u>{$product->name}</u></a>
                            because there was an error with the operation!";
        }
        return redirect()->route('products.index')
            ->with('alert-type', $alertType)
            ->with('alert-msg', $alertMsg);
    }

    public function destroyPhoto(Product $product): RedirectResponse
    {
        if ($this->deleteProductPhoto($product)) {
            return redirect()->back()
                ->with('alert-type', 'success')
                ->with('alert-msg', "Photo of product {$product->name} has been deleted.");
        } else {
            return redirect()->back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', "Photo of product {$product->name} does not exist.");
        }
    }

    private function deleteProductPhoto(Product $product): bool
    {
        if ($product->photo) {
            if (Storage::disk('public')->exists('products/' . $product->photo)) {
                Storage::disk('public')->delete('products/' . $product->photo);
                $product->photo = null;
                $product->save();
                return true;
            }
            $product->photo = null;
            $product->save();
        }
        return false;
    }

    private function deletePhotoFile(?string $photo_url): bool
    {
        if ($photo_url !== null) {
            if (Storage::disk('public')->exists('products/' . $photo_url)) {
                Storage::disk('public')->delete('products/' . $photo_url);
                return true;
            }
        }
        return false;
    }

    public function show(Product $product): View
    {

        $popularProducts = Product::withCount('item_orders')
                ->where('category_id','=',$product->category_id)
                ->where('id','!=',$product->id)
                ->orderBy('item_orders_count', 'desc')
                ->limit(5)
                ->get();


        $productsSameCategory = Product::query()
            ->where('products.category_id', $product->category_id)
            ->where('products.id', '!=', $product->id)
            ->limit(4)->get();
        return view('products.show')
        ->with('product', $product)
        ->with("PC", $popularProducts);
    }

    public function stock(Request $request): View
    {
        $selectedProducts = $request->get('product');
        $stockAction = $request->get('stock-action');
        $stockValue = $request->get('stock-value');

        $productsQuery = Product::query();

        $filterByCategory = $request->get('category');
        if ($filterByCategory && is_array($filterByCategory)) {
            $productsQuery->whereIntegerInRaw('products.category_id', $filterByCategory);
        } else $filterByCategory = null;

        $filterByStock = $request->get('stock');
        if ($filterByStock && is_array($filterByStock)) {
            $productsQuery->where(function (Builder $query) use ($filterByStock) {
                if (in_array(0, $filterByStock)) {
                    $query->orWhere('products.stock', '=', 0);
                }
                if (in_array(1, $filterByStock)) {
                    $query->orWhere(function (Builder $query) {
                        $query->where('products.stock', '!=', 0);
                        $query->whereColumn('products.stock', '<', 'stock_lower_limit');
                    });
                }
                if (in_array(2, $filterByStock)) {
                    $query->orWhere(function (Builder $query) {
                        $query->whereColumn('products.stock', '>=', 'stock_lower_limit');
                        $query->whereColumn('products.stock', '<=', 'stock_upper_limit');
                    });
                }
                if (in_array(3, $filterByStock)) {
                    $query->orWhereColumn('products.stock', '>', 'stock_upper_limit');
                }
            });
        } else $filterByStock = null;

        $search = $request->get('search');
        $searchWithRegex = $request->get('search-with-regex');
        if ($search) {
            if ($searchWithRegex) {
                $productsQuery->where('products.name', 'regexp', $search);
            } else {
                $productsQuery->where('products.name', 'like', "%$search%");
            }
        }

        $orderByElement = $request->get('order-element');
        $orderByDirection = $request->get('order-direction') ?? 'asc';
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
                ->paginate(20)
                ->withQueryString();
        } catch (\Exception $error) {
            $products = new LengthAwarePaginator([], 0, 1);
        }

        return view('products.stock')
            ->with('products', $products)
            ->with(compact('selectedProducts', 'stockAction', 'stockValue', 'filterByCategory', 'filterByStock', 'search', 'searchWithRegex', 'orderByElement', 'orderByDirection'));
    }

    public function updateStock(StockFormRequest $request): RedirectResponse
    {
        $filterByCategory = $request->get('category');
        $filterByStock = $request->get('stock');
        $search = $request->get('search');
        $searchWithRegex = $request->get('search-with-regex');
        $orderByElement = $request->get('order-element');
        $orderByDirection = $request->get('order-direction');

        $validatedData = $request->validated();
        $selectedProducts = $validatedData['product'];
        $stockAction = $validatedData['stock-action'];
        $stockValue = $validatedData['stock-value'];

        switch ($stockAction) {
            case 'add':
                DB::transaction(function () use ($selectedProducts, $stockValue) {
                    foreach ($selectedProducts as $productId) {
                        $product = Product::find($productId);
                        if ($product) {
                            $product->stock += $stockValue;
                            $product->save();

                            $newStockAdjustment = new Stock_adjustment();
                            $newStockAdjustment->product_id = $product->id;
                            $newStockAdjustment->quantity_changed = $stockValue;
                            $newStockAdjustment->registered_by_user_id = auth()->id();
                            $newStockAdjustment->save();
                        }
                    }
                });
                break;
            case 'remove':
                DB::transaction(function () use ($selectedProducts, $stockValue) {
                    foreach ($selectedProducts as $productId) {
                        $product = Product::find($productId);
                        if ($product) {
                            $newStock = max(0, $product->stock - $stockValue);
                            $oldStock = $product->stock;
                            $product->stock = $newStock;
                            $product->save();

                            $newStockAdjustment = new Stock_adjustment();
                            $newStockAdjustment->product_id = $product->id;
                            $newStockAdjustment->quantity_changed = $newStock - $oldStock;
                            $newStockAdjustment->registered_by_user_id = auth()->id();
                            $newStockAdjustment->save();
                        }
                    }
                });
                break;
            case 'set':
                DB::transaction(function () use ($selectedProducts, $stockValue) {
                    foreach ($selectedProducts as $productId) {
                        $product = Product::find($productId);
                        if ($product) {
                            $oldStock = $product->stock;
                            $product->stock = $stockValue;
                            $product->save();

                            $newStockAdjustment = new Stock_adjustment();
                            $newStockAdjustment->product_id = $product->id;
                            $newStockAdjustment->quantity_changed = $stockValue - $oldStock;
                            $newStockAdjustment->registered_by_user_id = auth()->id();
                            $newStockAdjustment->save();
                        }
                    }
                });
                break;
            default:
                return redirect()->back()
                    ->with('alert-type', 'danger')
                    ->with('alert-msg', "Invalid stock action: $stockAction");
        }

        return redirect()->route('products.stock',
            [
                'category' => $filterByCategory,
                'stock' => $filterByStock,
                'search' => $search,
                'search-with-regex' => $searchWithRegex,
                'order-element' => $orderByElement,
                'order-direction' => $orderByDirection,
                'product' => $selectedProducts,
                'stock-action' => $stockAction,
                'stock-value' => $stockValue
            ])
            ->with('alert-type', 'success')
            ->with('alert-msg', "Stock has been updated successfully!");
    }


    public function stockHistory(Request $request)
    {
        $adjustments = \App\Models\View_product_stock_logs::with(['product', 'user'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('products.stock_history', compact('adjustments'));
    }

}
