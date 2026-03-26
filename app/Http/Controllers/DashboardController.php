<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Summary
        |--------------------------------------------------------------------------
        */

        $totalSales = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        $totalOrders = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $newCustomers = DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.slug', 'customer')
            ->whereBetween('users.created_at', [$startDate, $endDate])
            ->count();

        $lowStockItems = DB::table('products')
            ->where('quantity', '<=', 20)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Latest Orders
        |--------------------------------------------------------------------------
        */

        $latestOrders = DB::table('payments')
            ->join('users', 'users.id', '=', 'payments.user_id')
            ->select(
                'payments.order_id',
                'users.name as customer_name',
                'payments.created_at as date',
                'payments.paid_amount',
                'payments.remaining_amount'

            )
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->latest('payments.order_id')
            ->limit(20)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Top 30 Selling Products
        |--------------------------------------------------------------------------
        */

        $topProducts = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(30)
            ->get();

        return response()->json([
            'dashboard_summary' => [
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'new_customers' => $newCustomers,
                'low_stock_items' => $lowStockItems,
            ],

            'latest_orders' => $latestOrders,

            'top_products' => $topProducts,
        ]);
    }

    public function summary(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth();

        // ===== PREVIOUS PERIOD =====
        $days = $startDate->diffInDays($endDate);
        $prevStart = (clone $startDate)->subDays($days + 1);
        $prevEnd = (clone $endDate)->subDays($days + 1);

        // ===== CURRENT =====
        $totalRevenue = DB::table('orders')->whereBetween('created_at', [$startDate, $endDate])->sum('total');
        $totalOrders = DB::table('orders')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalExpenses = DB::table('expenses')->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;

        // ===== PREVIOUS =====
        $prevRevenue = DB::table('orders')->whereBetween('created_at', [$prevStart, $prevEnd])->sum('total');
        $prevOrders = DB::table('orders')->whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevExpenses = DB::table('expenses')->whereBetween('created_at', [$prevStart, $prevEnd])->sum('amount');
        $prevProfit = $prevRevenue - $prevExpenses;

        // ===== GROWTH =====
        // $growth = function ($current, $previous) {
        //     if ($previous == 0) {
        //         return $current > 0 ? 100 : 0;
        //     }

        //     return round((($current - $previous) / $previous) * 100, 1);
        // };

        // =========================
        // 📊 SUMMARY (Dashboard Cards)
        // =========================
        $summary = [
            'revenue' => [
                'total' => (int) $totalRevenue,
                // 'growth' => $growth($totalRevenue, $prevRevenue),
            ],
            'expenses' => [
                'total' => (int) $totalExpenses,
                // 'growth' => $growth($totalExpenses, $prevExpenses),
            ],
            'Net Revenue' => [
                'total' => (int) $netProfit,
                // 'growth' => $growth($netProfit, $prevProfit),
            ],
            'orders' => [
                'total' => (int) $totalOrders,
                // 'growth' => $growth($totalOrders, $prevOrders),
            ],
        ];

        // =========================
        // 📈 SALES REPORT (NO %)
        // =========================
        $sales = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as orders'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('ROUND(SUM(total)/COUNT(id),2) as avg_order')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // =========================
        // 👨‍💼 CASHIER REPORT (NO %)
        // =========================
        $cashiers = DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.slug', 'cashier')
            ->pluck('users.name', 'users.id');

        $cashierData = DB::table('orders')
            ->select(
                'cashier_id',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('MIN(created_at) as first_order'),
                DB::raw('MAX(created_at) as last_order')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('cashier_id')
            ->get();

        $cashierReport = $cashierData->map(function ($item) use ($cashiers) {

            $hours = $item->first_order && $item->last_order
                ? Carbon::parse($item->last_order)->diffInHours(Carbon::parse($item->first_order)) + 1
                : 0;

            return [
                'cashier' => $cashiers[$item->cashier_id] ?? 'Unknown',
                'orders' => (int) $item->total_orders,
                'revenue' => (int) $item->total_revenue,
                'hours' => $hours.'h',
            ];
        });

        // =========================
        // 📦 PRODUCT REPORT (NO %)
        // =========================
        $productReport = DB::table('order_items')
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
            ->limit(10)
            ->get();

        $expenceReport = Expense::select('title', 'expense_date', 'amount', 'note')->get();

        return response()->json([
            'status' => true,
            'message' => 'Dashboard data fetched successfully',
            'data' => [
                'summary' => $summary,
                'reports' => [
                    'sales' => $sales,
                    'cashiers' => $cashierReport,
                    'products' => $productReport,
                    'Expenses' => $expenceReport,
                ],
            ],
        ]);
    }

    public function overdueCustomers(Request $request)
    {
        $overdue = DB::table('users')
            ->join('payment_histories as ph', 'users.id', '=', 'ph.user_id')

            ->select(
                'users.id as customer_id',
                'users.name as customer_name',
                DB::raw('MAX(CASE WHEN ph.debit > 0 THEN ph.date END) as last_debit'),
                DB::raw('MAX(CASE WHEN ph.credit > 0 THEN ph.date END) as last_credit'),
                DB::raw('SUM(ph.debit - ph.credit) as remaining'),
                DB::raw('DATEDIFF(NOW(), MAX(ph.date)) as days_overdue')
            )

            ->groupBy('users.id', 'users.name')

            ->havingRaw('remaining > 0')
            ->havingRaw('DATEDIFF(NOW(), MAX(ph.date)) > 30')

            ->orderByDesc('days_overdue')

            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Overdue customers',
            'data' => $overdue,
        ]);
    }

    public function lowStockProducts(Request $request)
    {
        $products = Product::select(
            'id',
            'name',
            'sku',
            'quantity',
            'selling_price',
            'buying_price',
            DB::raw("
                CASE
                    WHEN quantity < 10 THEN 'alert quantity less then 10'
                    WHEN quantity < 20 THEN 'warning quantity less then 20'
                END as stock_status
            ")
        )
            ->where('quantity', '<', 20)
            ->orderBy('quantity', 'asc')
            ->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Low stock products',
            'data' => $products,
        ]);
    }
}
