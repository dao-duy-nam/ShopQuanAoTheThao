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

    $validOrderStatuses = ['da_giao', 'da_nhan'];

    $revenue = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->whereIn('trang_thai_don_hang', $validOrderStatuses)
        ->sum('so_tien_thanh_toan');

    $lastMonthRevenue = Order::whereBetween('created_at', [
        $now->copy()->subMonth()->startOfMonth(),
        $now->copy()->subMonth()->endOfMonth()
    ])
        ->whereIn('trang_thai_don_hang', $validOrderStatuses)
        ->sum('so_tien_thanh_toan');


    $activeUsers = User::whereHas('orders', function ($query) use ($startOfMonth, $endOfMonth) {
        $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
    })->count();

    $lastMonthUsers = User::whereHas('orders', function ($query) use ($now) {
        $query->whereBetween('created_at', [
            $now->copy()->subMonth()->startOfMonth(),
            $now->copy()->subMonth()->endOfMonth()
        ]);
    })->count();


    $totalOrders = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

    $lastMonthOrders = Order::whereBetween('created_at', [
        $now->copy()->subMonth()->startOfMonth(),
        $now->copy()->subMonth()->endOfMonth()
    ])->count();


    $soldProducts = OrderDetail::whereHas('order', function ($q) use ($startOfMonth, $endOfMonth, $validOrderStatuses) {
        $q->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('trang_thai_don_hang', $validOrderStatuses);
    })->sum('so_luong');

    $soldProductsLastMonth = OrderDetail::whereHas('order', function ($q) use ($now, $validOrderStatuses) {
        $q->whereBetween('created_at', [
            $now->copy()->subMonth()->startOfMonth(),
            $now->copy()->subMonth()->endOfMonth()
        ])
            ->whereIn('trang_thai_don_hang', $validOrderStatuses);
    })->sum('so_luong');



    $thongKeTheoThang = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = $now->copy()->subMonths($i);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $revenue = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('trang_thai_don_hang', $validOrderStatuses)
            ->sum('so_tien_thanh_toan');

        $orders = Order::whereBetween('created_at', [$start, $end])->count();

        $activeUsers = User::whereHas('orders', function ($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        })->count();

        $soldProducts = OrderDetail::whereHas('order', function ($q) use ($start, $end, $validOrderStatuses) {
            $q->whereBetween('created_at', [$start, $end])
                ->whereIn('trang_thai_don_hang', $validOrderStatuses);
        })->sum('so_luong');

        $thongKeTheoThang[] = [
            'month' => 'Tháng ' . $month->month,
            'revenue' => (int) $revenue,
        ];
    }

    $dailyRevenue = [];
    for ($i = 0; $i < $now->daysInMonth; $i++) {
        $day = $startOfMonth->copy()->addDays($i);

        $revenue = Order::whereDate('created_at', $day)
            ->whereIn('trang_thai_don_hang', $validOrderStatuses)
            ->sum('so_tien_thanh_toan');

        $dailyRevenue[] = [
            'date' => $day->format('Y-m-d'),
            'revenue' => (float) $revenue
        ];
    }



    $yearlyRevenue = [];
    for ($i = 4; $i >= 0; $i--) {
        $year = $now->copy()->subYears($i)->year;
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = Carbon::create($year, 12, 31)->endOfYear();

        $revenue = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('trang_thai_don_hang', $validOrderStatuses)
            ->sum('so_tien_thanh_toan');

        $yearlyRevenue[] = [
            'year' => $year,
            'revenue' => (float) $revenue
        ];
    }

  $topSellingProducts = OrderDetail::selectRaw('san_pham_id, bien_the_id, SUM(so_luong) as total_quantity')
    ->whereHas('order', function ($q) use ($startOfMonth, $endOfMonth, $validOrderStatuses) {
        $q->whereBetween('created_at', [$startOfMonth, $endOfMonth])
          ->whereIn('trang_thai_don_hang', $validOrderStatuses);
    })
    ->groupBy('san_pham_id', 'bien_the_id')
    ->with(['product', 'variant']) 
    ->orderByDesc('total_quantity')
    ->take(5)
    ->get()
    ->map(function ($item) {
        return [
            'ten_san_pham' => $item->product->ten ?? "không rõ",
            'hinh_anh_bien_the' => $item->variant->hinh_anh ?? null,
            'so_luong_da_ban' => $item->total_quantity
        ];
    });

    return response()->json([
        'tong_doanh_thu' => round($revenue, 2),
        'nguoi_dung_hoat_dong' => $activeUsers,
        'tong_don_hang' => $totalOrders,
        'san_pham_da_ban' => $soldProducts,
        'doanh_thu_theo_thang' => $thongKeTheoThang,
        'doanh_thu_theo_ngay' => $dailyRevenue,
        'doanh_thu_theo_nam' => $yearlyRevenue,
        'san_pham_ban_chay' => $topSellingProducts
    ]);
}   

}
