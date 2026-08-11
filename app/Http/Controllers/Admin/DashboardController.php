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

        $recentOrders = Order::latest()->take(5)->get();

        $salesByDay = Order::where('created_at', '>=', now()->subDays(7))
            ->where('status', '!=', 'cancelled')
            ->select(DB::raw("date(created_at) as day"), DB::raw('sum(total) as total'))
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
            'recentOrders',
            'salesByDay'
        ));
    }
}