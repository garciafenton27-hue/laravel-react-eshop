<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        try {
            $stats = [
                'total_orders' => Order::count(),
                'revenue' => Order::where('status', 'completed')->sum('total') ?? 0,
                'total_products' => Product::count(),
                'pending_sellers' => User::where('email', 'like', '%seller%')->count(),
            ];

            // Daily/Weekly Orders
            $dailyOrders = Order::where('created_at', '>=', Carbon::now()->subDays(7))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as orders')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Revenue Trend (Last 30 days)
            $revenueTrend = Order::where('status', 'completed')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Product Performance
            $productPerformance = Product::with('category')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'stats' => $stats,
                'charts' => [
                    'daily_orders' => $dailyOrders,
                    'revenue_trend' => $revenueTrend,
                    'product_performance' => $productPerformance,
                ]
            ]);
        } catch (\Exception $e) {
            // Return fallback data if there's an error
            return response()->json([
                'stats' => [
                    'total_orders' => 0,
                    'revenue' => 0,
                    'total_products' => 0,
                    'pending_sellers' => 0,
                ],
                'charts' => [
                    'daily_orders' => [],
                    'revenue_trend' => [],
                    'product_performance' => [],
                ]
            ]);
        }
    }

    public function getSellerRequests()
    {
        $sellers = User::role('seller')
            ->where('is_verified', false)
            ->get(['id', 'name', 'email', 'created_at']);

        return response()->json($sellers);
    }

    public function approveSeller($id)
    {
        $seller = User::findOrFail($id);
        
        if (!$seller->hasRole('seller')) {
            return response()->json(['message' => 'User is not a seller'], 400);
        }

        $seller->update(['is_verified' => true]);

        return response()->json(['message' => 'Seller approved successfully']);
    }

    public function rejectSeller($id)
    {
        $seller = User::findOrFail($id);
        
        if (!$seller->hasRole('seller')) {
            return response()->json(['message' => 'User is not a seller'], 400);
        }

        $seller->update(['is_verified' => false]);

        return response()->json(['message' => 'Seller rejected']);
    }

    public function getOrders()
    {
        try {
            $orders = Order::with(['user'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['data' => []]);
        }
    }

    public function getProducts()
    {
        try {
            $products = Product::with('category')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['data' => []]);
        }
    }

    public function getCategories()
    {
        try {
            $categories = Category::orderBy('name')
                ->get();

            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}
