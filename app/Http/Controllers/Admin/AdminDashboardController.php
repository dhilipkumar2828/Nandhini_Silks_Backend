<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Real counts from database
        $totalSales = Schema::hasTable('orders') ? Order::where('order_status', 'delivered')->sum('grand_total') : 0;
        $totalOrders = Schema::hasTable('orders') ? Order::count() : 0;
        $totalUsers = User::count();
        $totalProducts = Product::count();

        // Basic Trends
        $salesTrend = "+12.5%";
        $ordersTrend = "+8.4%";
        $usersTrend = "+15.2%";
        $productsTrend = "+2.1%";

        // Recent Users
        $recentUsers = User::latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalSales', 
            'totalOrders', 
            'totalUsers', 
            'totalProducts',
            'salesTrend',
            'ordersTrend',
            'usersTrend',
            'productsTrend',
            'recentUsers'
        ));
    }
}
