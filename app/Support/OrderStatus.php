<?php

namespace App\Support;

/**
 * Định nghĩa tập trung các trạng thái đơn hàng, trạng thái thanh toán và quy tắc chuyển trạng thái.
 */
final class OrderStatus
{
    public const PREPARING = 'preparing';     // 1. Đang chuẩn bị hàng
    public const PREPARED = 'prepared';       // 2. Đã chuẩn bị hàng
    public const SHIPPING = 'shipping';       // 3. Đang giao hàng
    public const IN_TRANSIT = 'in_transit';   // 4. Đơn hàng trên đường tới
    public const DELIVERING = 'delivering';   // 5. Đang giao đến bạn
    public const COMPLETED = 'completed';     // 6. Hoàn tất
    public const CANCELLED = 'cancelled';     // 7. Đã hủy

    /**
     * Danh sách trạng thái đơn hàng theo đúng thứ tự nghiệp vụ đã chốt.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PREPARING => 'Đang chuẩn bị hàng',
            self::PREPARED => 'Đã chuẩn bị hàng',
            self::SHIPPING => 'Đang giao hàng',
            self::IN_TRANSIT => 'Đơn hàng trên đường tới',
            self::DELIVERING => 'Đang giao đến bạn',
            self::COMPLETED => 'Hoàn tất',
            self::CANCELLED => 'Đã hủy',
        ];
    }

    /**
     * Trả về tên hiển thị tiếng Việt của trạng thái đơn hàng (hỗ trợ cả các mã cũ nếu có).
     */
    public static function label(string $status): string
    {
        $map = self::labels() + [
            'pending' => 'Đang chuẩn bị hàng',
            'confirmed' => 'Đã chuẩn bị hàng',
            'processing' => 'Đã chuẩn bị hàng',
            'shipped' => 'Đang giao hàng',
        ];

        return $map[$status] ?? $status;
    }

    /**
     * Màu badge Bootstrap tương ứng với từng trạng thái đơn hàng.
     */
    public static function badgeClass(string $status): string
    {
        return match ($status) {
            self::PREPARING, 'pending' => 'bg-warning text-dark',
            self::PREPARED, 'confirmed', 'processing' => 'bg-info text-dark',
            self::SHIPPING, self::IN_TRANSIT, self::DELIVERING, 'shipped' => 'bg-primary',
            self::COMPLETED => 'bg-success',
            self::CANCELLED => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Khách hàng CHỈ ĐƯỢC HỦY đơn khi đơn ở trạng thái "Đang chuẩn bị hàng" hoặc "Đã chuẩn bị hàng".
     */
    public static function canCustomerCancel(string $status): bool
    {
        return in_array($status, [
            self::PREPARING,
            self::PREPARED,
            'pending',
            'confirmed',
        ], true);
    }

    /**
     * Kiểm tra đơn hàng có thể bị hủy bởi quản trị viên hay không (chưa hoàn tất và chưa hủy).
     */
    public static function canAdminCancel(string $status): bool
    {
        return !in_array($status, [self::COMPLETED, self::CANCELLED], true);
    }

    /**
     * Nhãn hiển thị phương thức thanh toán.
     */
    public static function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cod' => 'Thanh toán khi nhận hàng (COD)',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            default => $method,
        };
    }

    /**
     * Nhãn hiển thị trạng thái thanh toán (Chưa thanh toán / Đã thanh toán).
     */
    public static function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Đã thanh toán',
            'pending' => 'Chưa thanh toán',
            'failed' => 'Thanh toán thất bại',
            'refunded' => 'Đã hoàn tiền',
            default => $status,
        };
    }

    /**
     * Màu badge Bootstrap cho trạng thái thanh toán.
     */
    public static function paymentBadgeClass(string $status): string
    {
        return match ($status) {
            'paid' => 'bg-success',
            'pending' => 'bg-warning text-dark',
            'failed' => 'bg-danger',
            'refunded' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }
}
