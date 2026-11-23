<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->toDateTimeString();
        $currentTime = $now->toDateTimeString();

        $monthlyRevenue = DB::table('orders')
            ->where('TrangThai', OrderStatus::COMPLETED)
            ->whereBetween(DB::raw('COALESCE(DatLuc, NgayTao)'), [$monthStart, $currentTime])
            ->sum('TongTien');

        $pendingOrderCount = DB::table('orders')
            ->whereIn('TrangThai', [
                OrderStatus::PREPARING,
                OrderStatus::PREPARED,
                'pending',
                'confirmed',
                'processing',
            ])
            ->count();

        $activeProductCount = DB::table('products')
            ->where('TrangThai', 'active')
            ->whereNull('NgayXoa')
            ->count();

        $customerCount = DB::table('users as u')
            ->join('user_roles as ur', 'ur.MaNguoiDung', '=', 'u.MaNguoiDung')
            ->join('roles as r', 'r.MaVaiTro', '=', 'ur.MaVaiTro')
            ->where('r.TenVaiTro', 'customer')
            ->where('u.TrangThai', 'active')
            ->whereNull('u.NgayXoa')
            ->distinct()
            ->count('u.MaNguoiDung');

        $lowStockCount = DB::table('products')
            ->whereIn('TrangThai', ['active', 'out_of_stock'])
            ->whereNull('NgayXoa')
            ->where('SoLuongTon', '<=', 5)
            ->count();

        $recentOrders = DB::table('orders as o')
            ->leftJoin('users as u', 'u.MaNguoiDung', '=', 'o.MaNguoiDung')
            ->select([
                'o.MaDonHang',
                'o.SoDonHang',
                'o.TenNguoiNhan',
                'o.TongTien',
                'o.TrangThai',
                'o.DatLuc',
                'o.NgayTao',
                'u.HoTen as TenKhachHang',
            ])
            ->orderByRaw('COALESCE(o.DatLuc, o.NgayTao) DESC')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'monthlyRevenue' => (float) $monthlyRevenue,
            'pendingOrderCount' => $pendingOrderCount,
            'activeProductCount' => $activeProductCount,
            'customerCount' => $customerCount,
            'lowStockCount' => $lowStockCount,
            'recentOrders' => $recentOrders,
            'monthLabel' => $now->format('m/Y'),
        ]);
    }
}
