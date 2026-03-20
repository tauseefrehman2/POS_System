<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth();

        // Previous period (for percentage comparison)
        $days = $startDate->diffInDays($endDate);
        $prevStart = (clone $startDate)->subDays($days + 1);
        $prevEnd = (clone $endDate)->subDays($days + 1);

        // ===== CURRENT DATA =====
        $totalRevenue = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        $totalOrders = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalExpenses = DB::table('expenses')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $netProfit = $totalRevenue - $totalExpenses;

        // ===== PREVIOUS DATA =====
        $prevRevenue = DB::table('orders')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total');

        $prevOrders = DB::table('orders')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $prevExpenses = DB::table('expenses')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('amount');

        $prevProfit = $prevRevenue - $prevExpenses;

        // ===== PERCENTAGE FUNCTION =====
        $calculateGrowth = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }

            return round((($current - $previous) / $previous) * 100, 1);
        };

        return response()->json([
            'revenue' => [
                'total' => $totalRevenue,
                'growth' => $calculateGrowth($totalRevenue, $prevRevenue),
            ],
            'expenses' => [
                'total' => $totalExpenses,
                'growth' => $calculateGrowth($totalExpenses, $prevExpenses),
            ],
            'profit' => [
                'total' => $netProfit,
                'growth' => $calculateGrowth($netProfit, $prevProfit),
            ],
            'orders' => [
                'total' => $totalOrders,
                'growth' => $calculateGrowth($totalOrders, $prevOrders),
            ],
        ]);
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth();

        $sales = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as orders'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('ROUND(SUM(total)/COUNT(id), 2) as avg_order')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json($sales);
    }

    public function cashierReport(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        // Cashiers
        $cashiers = DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.slug', 'cashier')
            ->pluck('users.name', 'users.id');
        // Orders summary per cashier
        $orders = DB::table('orders')
            ->select(
                'cashier_id',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('MIN(created_at) as first_order'),
                DB::raw('MAX(created_at) as last_order')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('cashier_id')
            ->paginate(10);
        // dd($orders->toArray());

        $report = $orders->map(function ($item) use ($cashiers) {

            $workingHours = ($item->first_order && $item->last_order)
                ? Carbon::parse($item->last_order)
                    ->diffInHours(Carbon::parse($item->first_order)) + 1
                : 0;

            return [
                'cashier' => $cashiers[$item->cashier_id] ?? 'Unknown',
                'orders' => (int) $item->total_orders,
                'revenue' => (int) $item->total_revenue,
                'hours' => $workingHours.'h',
            ];
        });

        return response()->json($report);
    }

    public function productReport(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $products = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->select(
                'order_items.product_name',

                DB::raw('SUM(order_items.quantity) as qty_sold'),

                DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),

                DB::raw('SUM((order_items.price - order_items.buying_price) * order_items.quantity) as profit')
            )
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('qty_sold')
            ->paginate(10);

        $report = $products->map(function ($item) {
            return [
                'product' => $item->product_name,
                'qty_sold' => (int) $item->qty_sold,
                'revenue' => (int) $item->revenue,
                'profit' => (int) $item->profit,
            ];
        });

        return response()->json($report);
    }
}
