<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service quản lý Mã giảm giá phía quản trị (B5) và kiểm tra điều kiện áp dụng mã cho Giỏ hàng (A5).
 */
class CouponManagementService
{
    /**
     * Lấy danh sách mã giảm giá có tìm kiếm, lọc và phân trang.
     */
    public function paginateCoupons(
        ?string $keyword = null,
        ?string $status = null,
        ?string $type = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = DB::table('coupons')->select([
            'MaPhieuGiamGia',
            'MaUuDai',
            'LoaiGiamGia',
            'GiaTriGiam',
            'DonHangToiThieu',
            'SoLanSuDungToiDa',
            'SoLanSuDungToiDaMoiNguoi',
            'SoLanDaSuDung',
            'BatDauLuc',
            'KetThucLuc',
            'TrangThai',
            'NgayTao',
            'NgayCapNhat',
        ]);

        $trimmed = trim((string) $keyword);
        if ($trimmed !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $trimmed);
            $query->where('MaUuDai', 'like', '%' . $escaped . '%');
        }

        if ($status !== null && in_array($status, ['active', 'inactive'], true)) {
            $query->where('TrangThai', $status);
        }

        if ($type !== null && in_array($type, ['percent', 'fixed'], true)) {
            $query->where('LoaiGiamGia', $type);
        }

        return $query
            ->orderByDesc('NgayCapNhat')
            ->orderByDesc('MaPhieuGiamGia')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): ?object
    {
        return DB::table('coupons')->where('MaPhieuGiamGia', $id)->first();
    }

    /**
     * Tạo mã giảm giá mới.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $now = Carbon::now();

        return (int) DB::table('coupons')->insertGetId([
            'MaUuDai' => mb_strtoupper(trim((string) $data['MaUuDai'])),
            'LoaiGiamGia' => $data['LoaiGiamGia'],
            'GiaTriGiam' => (float) $data['GiaTriGiam'],
            'DonHangToiThieu' => (float) ($data['DonHangToiThieu'] ?? 0),
            'SoLanSuDungToiDa' => !empty($data['SoLanSuDungToiDa']) ? (int) $data['SoLanSuDungToiDa'] : null,
            'SoLanSuDungToiDaMoiNguoi' => !empty($data['SoLanSuDungToiDaMoiNguoi']) ? (int) $data['SoLanSuDungToiDaMoiNguoi'] : null,
            'SoLanDaSuDung' => 0,
            'BatDauLuc' => Carbon::parse((string) $data['BatDauLuc'])->format('Y-m-d H:i:s'),
            'KetThucLuc' => Carbon::parse((string) $data['KetThucLuc'])->format('Y-m-d H:i:s'),
            'TrangThai' => $data['TrangThai'],
            'NgayTao' => $now,
            'NgayCapNhat' => $now,
        ]);
    }

    /**
     * Cập nhật thông tin mã giảm giá.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $now = Carbon::now();

        $affected = DB::table('coupons')
            ->where('MaPhieuGiamGia', $id)
            ->update([
                'MaUuDai' => mb_strtoupper(trim((string) $data['MaUuDai'])),
                'LoaiGiamGia' => $data['LoaiGiamGia'],
                'GiaTriGiam' => (float) $data['GiaTriGiam'],
                'DonHangToiThieu' => (float) ($data['DonHangToiThieu'] ?? 0),
                'SoLanSuDungToiDa' => !empty($data['SoLanSuDungToiDa']) ? (int) $data['SoLanSuDungToiDa'] : null,
                'SoLanSuDungToiDaMoiNguoi' => !empty($data['SoLanSuDungToiDaMoiNguoi']) ? (int) $data['SoLanSuDungToiDaMoiNguoi'] : null,
                'BatDauLuc' => Carbon::parse((string) $data['BatDauLuc'])->format('Y-m-d H:i:s'),
                'KetThucLuc' => Carbon::parse((string) $data['KetThucLuc'])->format('Y-m-d H:i:s'),
                'TrangThai' => $data['TrangThai'],
                'NgayCapNhat' => $now,
            ]);

        return $affected >= 0;
    }

    /**
     * Xóa mã giảm giá. Nếu mã đã có lượt sử dụng trong đơn hàng (coupon_redemptions / orders),
     * chuyển sang trạng thái 'inactive' để bảo toàn lịch sử thay vì xóa cứng.
     *
     * @return array{status: string, message: string}
     */
    public function delete(int $id): array
    {
        $coupon = $this->findById($id);
        if ($coupon === null) {
            return [
                'status' => 'not_found',
                'message' => 'Không tìm thấy mã giảm giá.',
            ];
        }

        $hasRedemptions = DB::table('coupon_redemptions')
            ->where('MaPhieuGiamGia', $id)
            ->exists();

        $hasOrders = DB::table('orders')
            ->where('MaPhieuGiamGia', $id)
            ->exists();

        if ((int) $coupon->SoLanDaSuDung > 0 || $hasRedemptions || $hasOrders) {
            DB::table('coupons')
                ->where('MaPhieuGiamGia', $id)
                ->update([
                    'TrangThai' => 'inactive',
                    'NgayCapNhat' => Carbon::now(),
                ]);

            return [
                'status' => 'deactivated',
                'message' => 'Mã giảm giá đã từng được sử dụng trong đơn hàng nên đã được chuyển sang trạng thái Ngừng kích hoạt để bảo toàn lịch sử.',
            ];
        }

        DB::table('coupons')->where('MaPhieuGiamGia', $id)->delete();

        return [
            'status' => 'deleted',
            'message' => 'Đã xóa mã giảm giá thành công.',
        ];
    }

    /**
     * Kiểm tra điều kiện sử dụng mã giảm giá và tính số tiền được giảm trên tổng tạm tính ($subtotal).
     * Giá khuyến mãi theo sản phẩm đã được áp trước vào $subtotal, mã giảm giá áp lên tổng sau đó.
     *
     * @return array{valid: bool, message: string, coupon?: object, discount_amount: float}
     */
    public function validateAndCalculateDiscount(
        string $rawCode,
        float $subtotal,
        ?int $userId = null
    ): array {
        $code = mb_strtoupper(trim($rawCode));
        if ($code === '') {
            return [
                'valid' => false,
                'message' => 'Vui lòng nhập mã giảm giá.',
                'discount_amount' => 0.0,
            ];
        }

        $coupon = DB::table('coupons')->where('MaUuDai', $code)->first();

        if ($coupon === null || $coupon->TrangThai !== 'active') {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá không tồn tại hoặc đang ngừng hoạt động.',
                'discount_amount' => 0.0,
            ];
        }

        $now = Carbon::now();
        if ($now->lt(Carbon::parse((string) $coupon->BatDauLuc))) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá này chưa đến thời gian có hiệu lực.',
                'discount_amount' => 0.0,
            ];
        }

        if ($now->gt(Carbon::parse((string) $coupon->KetThucLuc))) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá này đã hết hạn sử dụng.',
                'discount_amount' => 0.0,
            ];
        }

        if ($coupon->SoLanSuDungToiDa !== null && (int) $coupon->SoLanDaSuDung >= (int) $coupon->SoLanSuDungToiDa) {
            return [
                'valid' => false,
                'message' => 'Mã giảm giá này đã hết lượt sử dụng.',
                'discount_amount' => 0.0,
            ];
        }

        $minOrder = (float) $coupon->DonHangToiThieu;
        if ($subtotal < $minOrder) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Đơn hàng tối thiểu từ %s đ mới được áp dụng mã %s.',
                    number_format($minOrder, 0, ',', '.'),
                    $coupon->MaUuDai
                ),
                'discount_amount' => 0.0,
            ];
        }

        if ($userId !== null && $coupon->SoLanSuDungToiDaMoiNguoi !== null) {
            $usedByUser = DB::table('coupon_redemptions')
                ->where('MaPhieuGiamGia', $coupon->MaPhieuGiamGia)
                ->where('MaNguoiDung', $userId)
                ->count();

            if ($usedByUser >= (int) $coupon->SoLanSuDungToiDaMoiNguoi) {
                return [
                    'valid' => false,
                    'message' => 'Bạn đã sử dụng hết số lượt cho phép của mã giảm giá này.',
                    'discount_amount' => 0.0,
                ];
            }
        }

        $discountValue = (float) $coupon->GiaTriGiam;
        $rawDiscount = match ($coupon->LoaiGiamGia) {
            'percent' => round($subtotal * ($discountValue / 100), 2),
            'fixed' => $discountValue,
            default => 0.0,
        };

        $discountAmount = min($subtotal, max(0.0, $rawDiscount));

        return [
            'valid' => true,
            'message' => sprintf('Đã áp dụng mã giảm giá %s.', $coupon->MaUuDai),
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
        ];
    }
}

