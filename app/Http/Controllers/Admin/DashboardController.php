<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalUsers = User::count();
        $totalProducts = Product::count();

        $lowStockVariants = Variant::where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->with('product')
            ->get();

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $recentOrders = Order::latest()->take(5)->get();

        $salesByDay = Order::where('created_at', '>=', now()->subDays(7))
            ->where('status', '!=', 'cancelled')
            ->select(DB::raw('date(created_at) as day'), DB::raw('sum(total) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'totalUsers',
            'totalProducts',
            'lowStockVariants',
            'topProducts',
            'recentOrders',
            'salesByDay'
        ));
    }
}
