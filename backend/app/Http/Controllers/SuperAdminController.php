<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_admins' => User::where('email', 'like', '%admin%')->count(),
                'total_sellers' => User::where('email', 'like', '%seller%')->count(),
                'total_orders' => Order::count(),
                'total_revenue' => Order::where('status', 'completed')->sum('total') ?? 0,
                'pending_sellers' => User::where('email', 'like', '%seller%')->count(),
            ];

            // Monthly Revenue (Last 12 months)
            $monthlyRevenue = Order::where('status', 'completed')
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total) as revenue')
                ->groupBy('month', 'year')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            // Orders vs Revenue (Last 30 days)
            $ordersRevenue = Order::where('created_at', '>=', Carbon::now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // User Growth (Last 6 months)
            $userGrowth = User::where('created_at', '>=', Carbon::now()->subMonths(6))
                ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as users')
                ->groupBy('month', 'year')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            // Seller Performance
            $sellerPerformance = User::where('email', 'like', '%seller%')
                ->get()
                ->map(function($seller) {
                    return [
                        'name' => $seller->name,
                        'email' => $seller->email,
                        'products_count' => 0,
                        'total_sales' => 0,
                        'is_verified' => true,
                    ];
                })
                ->take(10);

            return response()->json([
                'stats' => $stats,
                'charts' => [
                    'monthly_revenue' => $monthlyRevenue,
                    'orders_revenue' => $ordersRevenue,
                    'user_growth' => $userGrowth,
                    'seller_performance' => $sellerPerformance,
                ]
            ]);
        } catch (\Exception $e) {
            // Return fallback data if there's an error
            return response()->json([
                'stats' => [
                    'total_users' => 0,
                    'total_admins' => 0,
                    'total_sellers' => 0,
                    'total_orders' => 0,
                    'total_revenue' => 0,
                    'pending_sellers' => 0,
                ],
                'charts' => [
                    'monthly_revenue' => [],
                    'orders_revenue' => [],
                    'user_growth' => [],
                    'seller_performance' => [],
                ]
            ]);
        }
    }

    public function getUsers()
    {
        try {
            $users = User::orderBy('created_at', 'desc')->get();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function getAdmins()
    {
        try {
            $admins = User::where('email', 'like', '%admin%')->get();
            return response()->json($admins);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function getSellers()
    {
        try {
            $sellers = User::where('email', 'like', '%seller%')->get();
            return response()->json($sellers);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function createAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $admin->assignRole('admin');

        return response()->json([
            'message' => 'Admin created successfully',
            'admin' => $admin->load('roles')
        ], 201);
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

    public function blockUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_blocked' => true]);

        return response()->json(['message' => 'User blocked successfully']);
    }

    public function unblockUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_blocked' => false]);

        return response()->json(['message' => 'User unblocked successfully']);
    }

    public function deleteAdmin($id)
    {
        $admin = User::findOrFail($id);
        
        if (!$admin->hasRole('admin')) {
            return response()->json(['message' => 'User is not an admin'], 400);
        }

        if ($admin->hasRole('super_admin')) {
            return response()->json(['message' => 'Cannot delete super admin'], 403);
        }

        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully']);
    }

    public function getAllOrders()
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($orders);
    }

    public function getSystemSettings()
    {
        // This would typically come from a settings table
        return response()->json([
            'settings' => [
                'site_name' => 'E-Commerce Platform',
                'maintenance_mode' => false,
                'registration_enabled' => true,
                'seller_verification_required' => true,
            ]
        ]);
    }
}
