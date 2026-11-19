<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service tổng hợp số liệu thống kê doanh thu, sản phẩm bán chạy và cảnh báo tồn kho (B7).
 */
class ReportService
{
    /**
     * Tổng hợp dữ liệu báo cáo cho trang quản trị.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getDashboardData(array $filters): array
    {
        $period = ($filters['period'] ?? 'daily') === 'monthly' ? 'monthly' : 'daily';
        $threshold = isset($filters['low_stock_threshold']) && $filters['low_stock_threshold'] !== ''
            ? (int) $filters['low_stock_threshold']
            : 5;

        $defaultFrom = $period === 'monthly'
            ? Carbon::now()->subMonths(5)->startOfMonth()
            : Carbon::now()->subDays(29)->startOfDay();

        $from = !empty($filters['from_date'])
            ? Carbon::parse((string) $filters['from_date'])->startOfDay()
            : $defaultFrom;

        $to = !empty($filters['to_date'])
            ? Carbon::parse((string) $filters['to_date'])->endOfDay()
            : Carbon::now()->endOfDay();

        $revenueSeries = $this->getRevenueSeries($period, $from, $to);
        $topProducts = $this->getTopSellingProducts($from, $to, 10);
        $lowStockProducts = $this->getLowStockProducts($threshold, 20);

        return [
            'filters' => [
                'period' => $period,
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'low_stock_threshold' => $threshold,
            ],
            'summary' => [
                'tong_doanh_thu' => (float) $revenueSeries->sum('doanh_thu'),
                'tong_don_hoan_thanh' => (int) $revenueSeries->sum('so_don'),
                'so_sp_sap_het' => $lowStockProducts->count(),
            ],
            'revenueSeries' => $revenueSeries,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
        ];
    }

    /**
     * Doanh thu theo ngày hoặc tháng từ các đơn hàng đã hoàn thành.
     *
     * @return Collection<int, object>
     */
    private function getRevenueSeries(string $period, Carbon $from, Carbon $to): Collection
    {
        $groupExpr = $period === 'monthly'
            ? "DATE_FORMAT(COALESCE(DatLuc, NgayTao), '%Y-%m')"
            : 'DATE(COALESCE(DatLuc, NgayTao))';

        return DB::table('orders')
            ->selectRaw("{$groupExpr} as nhan_thoi_gian, SUM(TongTien) as doanh_thu, COUNT(*) as so_don")
            ->where('TrangThai', 'completed')
            ->whereBetween(DB::raw('COALESCE(DatLuc, NgayTao)'), [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
            ->groupByRaw($groupExpr)
            ->orderBy('nhan_thoi_gian')
            ->get();
    }

    /**
     * Top sản phẩm bán chạy theo tổng số lượng từ đơn hoàn thành.
     *
     * @return Collection<int, object>
     */
    private function getTopSellingProducts(Carbon $from, Carbon $to, int $limit): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.MaDonHang', '=', 'order_items.MaDonHang')
            ->selectRaw('order_items.MaSanPham, order_items.TenSanPham, order_items.MaSKU, SUM(order_items.SoLuong) as tong_so_luong, SUM(order_items.ThanhTien) as tong_doanh_thu')
            ->where('orders.TrangThai', 'completed')
            ->whereBetween(DB::raw('COALESCE(orders.DatLuc, orders.NgayTao)'), [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
            ->groupBy('order_items.MaSanPham', 'order_items.TenSanPham', 'order_items.MaSKU')
            ->orderByDesc('tong_so_luong')
            ->limit($limit)
            ->get();
    }

    /**
     * Danh sách sản phẩm sắp hết hàng (SoLuongTon <= ngưỡng).
     *
     * @return Collection<int, object>
     */
    private function getLowStockProducts(int $threshold, int $limit): Collection
    {
        return DB::table('products')
            ->join('categories', 'categories.MaDanhMuc', '=', 'products.MaDanhMuc')
            ->select([
                'products.MaSanPham',
                'products.MaSKU',
                'products.TenSanPham',
                'products.SoLuongTon',
                'products.TrangThai',
                'categories.TenDanhMuc',
            ])
            ->whereNull('products.NgayXoa')
            ->whereIn('products.TrangThai', ['active', 'out_of_stock'])
            ->where('products.SoLuongTon', '<=', $threshold)
            ->orderBy('products.SoLuongTon')
            ->orderBy('products.MaSanPham')
            ->limit($limit)
            ->get();
    }
}
