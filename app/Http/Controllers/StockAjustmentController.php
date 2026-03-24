<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockFormRequest;
use App\Models\Order;
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
use App\Http\Requests\CartConfirmationFormRequest;
use Illuminate\Support\Facades\Auth;
use Termwind\Components\Raw;

class StockAjustmentController extends Controller
{

    public function index(Request $request): View
    {
        //$adjusts = Stock_adjustment::query()
        $adjusts = Stock_adjustment::with(['product', 'user'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('stockAdjust.index', compact('adjusts'));
    }

    public function show(Stock_adjustment $adjust): View
    {
        return view('stockAdjust.show')->with('adjust', $adjust);
    }
    public function destroy(Stock_adjustment $adjust): RedirectResponse
    {
        //$adjust->delete();
        return Redirect::route('stockAdjust.index')->with('success', 'Stock adjustment deleted successfully.');
    }
}
