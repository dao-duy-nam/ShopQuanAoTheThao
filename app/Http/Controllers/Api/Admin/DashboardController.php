<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderDetail;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Tổng doanh thu tháng này
        $revenue = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('trang_thai_don_hang', '!=', 'da_huy')
            ->sum('so_tien_thanh_toan');

        // Doanh thu tháng trước
        $lastMonthRevenue = Order::whereBetween('created_at', [
            $now->copy()->subMonth()->startOfMonth(),
            $now->copy()->subMonth()->endOfMonth()
        ])
        ->where('trang_thai_don_hang', '!=', 'da_huy')
        ->sum('so_tien_thanh_toan');

        $revenueChange = $lastMonthRevenue == 0 ? null : (($revenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;

        // Người dùng hoạt động trong tháng
        $activeUsers = User::whereHas('orders', function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        })->count();

        $lastMonthUsers = User::whereHas('orders', function ($query) use ($now) {
            $query->whereBetween('created_at', [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth()
            ]);
        })->count();

        $userChange = $lastMonthUsers == 0 ? null : (($activeUsers - $lastMonthUsers) / $lastMonthUsers) * 100;

        // Tổng đơn hàng tháng này
        $totalOrders = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        $lastMonthOrders = Order::whereBetween('created_at', [
            $now->copy()->subMonth()->startOfMonth(),
            $now->copy()->subMonth()->endOfMonth()
        ])->count();

        $orderChange = $lastMonthOrders == 0 ? null : (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100;

        // Sản phẩm đã bán
        $soldProducts = OrderDetail::whereHas('order', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('created_at', [$startOfMonth, $endOfMonth])
              ->where('trang_thai_don_hang', '!=', 'da_huy');
        })->sum('so_luong');

        $soldProductsLastMonth = OrderDetail::whereHas('order', function ($q) use ($now) {
            $q->whereBetween('created_at', [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth()
            ])
            ->where('trang_thai_don_hang', '!=', 'da_huy');
        })->sum('so_luong');

        $productChange = $soldProductsLastMonth == 0 ? null : (($soldProducts - $soldProductsLastMonth) / $soldProductsLastMonth) * 100;

        // Doanh thu 6 tháng gần nhất
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthlyRevenue[] = [
                'month' => 'Tháng ' . $month->month,
                'revenue' => Order::whereBetween('created_at', [
                    $month->startOfMonth(),
                    $month->endOfMonth()
                ])->where('trang_thai_don_hang', '!=', 'da_huy')
                ->sum('so_tien_thanh_toan')
            ];
        }

        // Hoạt động gần đây từ bảng activity_logs
        $recentActivities = ActivityLog::latest()
            ->with('user')
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'user' => $log->user->name ?? 'Không rõ',
                    'action' => $log->action,
                    'time' => $log->created_at->diffForHumans(),
                    'status' => $log->status
                ];
            });

        return response()->json([
            'tong_doanh_thu' => round($revenue, 2),
            'ty_le_tang_truong_doanh_thu' => round($revenueChange, 1),
            'nguoi_dung_hoat_dong' => $activeUsers,
            'ty_le_tang_truong_nguoi_dung' => round($userChange, 1),
            'tong_don_hang' => $totalOrders,
            'ty_le_tang_truong_don_hang' => round($orderChange, 1),
            'san_pham_da_ban' => $soldProducts,
            'ty_le_tang_truong_san_pham' => round($productChange, 1),
            'doanh_thu_theo_thang' => $monthlyRevenue,
            'hoat_dong_gan_day' => $recentActivities
        ]);
    }
}
