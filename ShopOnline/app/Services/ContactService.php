<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Service xử lý gửi thông tin liên hệ từ khách hàng (A8).
 */
class ContactService
{
    /**
     * Gửi email thông báo liên hệ (nếu đã cấu hình mail) và ghi log vận hành.
     *
     * @param array<string, mixed> $data
     */
    public function sendInquiry(array $data): void
    {
        $recipient = config('mail.from.address');
        $body = sprintf(
            "Họ tên: %s\nEmail: %s\nSố điện thoại: %s\nChủ đề: %s\n\nNội dung:\n%s",
            $data['ho_ten'],
            $data['email'],
            $data['so_dien_thoai'] ?? 'Không cung cấp',
            $data['chu_de'],
            $data['noi_dung']
        );

        if (is_string($recipient) && $recipient !== '') {
            try {
                Mail::raw($body, function ($message) use ($recipient, $data): void {
                    $message->to($recipient)
                        ->replyTo((string) $data['email'], (string) $data['ho_ten'])
                        ->subject('[Liên hệ] ' . $data['chu_de']);
                });
            } catch (Throwable $exception) {
                Log::warning('Không thể gửi email liên hệ qua SMTP: ' . $exception->getMessage());
            }
        }

        Log::info('Đã tiếp nhận yêu cầu liên hệ mới.', [
            'chu_de' => $data['chu_de'],
        ]);
    }
}
