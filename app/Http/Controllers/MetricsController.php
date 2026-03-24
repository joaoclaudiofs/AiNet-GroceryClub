<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Operation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function transactionRecords()
    {
        $transactions = Order::latest()->paginate(20);
        return view('metrics.transactionRecords', compact('transactions'));
    }


    public function salesPerformance()
    {
        $yearMonthSelect = DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', created_at) AS INTEGER) as year, CAST(strftime('%m', created_at) AS INTEGER) as month"
            : 'YEAR(created_at) as year, MONTH(created_at) as month';

        $salesRaw = \App\Models\Order::selectRaw("{$yearMonthSelect}, SUM(total) as total_sales, COUNT(*) as sales_count")
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $years = $salesRaw->pluck('year')->unique()->sortDesc()->values();

        $salesByMonth = collect();
        foreach ($years as $year) {
            foreach (range(1, 12) as $month) {
                $record = $salesRaw->first(fn($r) => $r->year == $year && $r->month == $month);
                $salesByMonth->push((object)[
                    'year' => $year,
                    'month' => $month,
                    'total_sales' => $record ? $record->total_sales : 0,
                    'sales_count' => $record ? $record->sales_count : 0,
                ]);
            }
        }

        return view('metrics.salesPerformance', compact('salesByMonth'));
    }

    public function membershipTrends()
    {
        $type = 'Board';
        $yearMonthSelect = DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', created_at) AS INTEGER) as year, CAST(strftime('%m', created_at) AS INTEGER) as month"
            : 'YEAR(created_at) as year, MONTH(created_at) as month';

        $membersByMonth = User::selectRaw("{$yearMonthSelect}, COUNT(*) as total_members")

            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');
        if($type){
            $membersByMonth->where('type',  $type);
            $members = User::where('type', $type)->get();
        }
        else{
            $members = User::get();
        }
        $membersByMonth = $membersByMonth->get();

        return view('metrics.membershipTrends', compact('membersByMonth', 'members'));
    }
}
