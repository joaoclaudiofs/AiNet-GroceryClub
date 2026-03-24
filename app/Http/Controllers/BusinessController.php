<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BusinessController extends Controller
{
    public function settings(Request $request)
    {
        return view('business.settings');
    }
}