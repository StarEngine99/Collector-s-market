<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductReviewController extends Controller
{
    /**
     * Gửi đánh giá cho sản phẩm (1-5 sao và bình luận).
     */
    public function store(Request $request, int $productId): RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để gửi đánh giá sản phẩm.');
        }

        $validated = $request->validate([
            'SoSao' => ['required', 'integer', 'between:1,5'],
            'NoiDung' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'SoSao.required' => 'Vui lòng chọn số sao đánh giá (1-5 sao).',
            'SoSao.between' => 'Số sao hợp lệ từ 1 đến 5 sao.',
            'NoiDung.required' => 'Vui lòng nhập nội dung đánh giá của bạn.',
            'NoiDung.min' => 'Nội dung đánh giá cần ít nhất 3 ký tự.',
        ]);

        $userId = (int) $user->getKey();

        // Kiểm tra xem đã đánh giá sản phẩm này chưa
        $existing = DB::table('product_reviews')
            ->where('MaNguoiDung', $userId)
            ->where('MaSanPham', $productId)
            ->first();

        $now = Carbon::now();

        if ($existing) {
            DB::table('product_reviews')
                ->where('MaDanhGia', $existing->MaDanhGia)
                ->update([
                    'SoSao' => $validated['SoSao'],
                    'NoiDung' => $validated['NoiDung'],
                    'TrangThai' => 'published',
                    'NgayCapNhat' => $now,
                ]);
            $message = 'Đã cập nhật đánh giá của bạn thành công!';
        } else {
            DB::table('product_reviews')->insert([
                'MaSanPham' => $productId,
                'MaNguoiDung' => $userId,
                'SoSao' => $validated['SoSao'],
                'NoiDung' => $validated['NoiDung'],
                'TrangThai' => 'published',
                'NgayTao' => $now,
                'NgayCapNhat' => $now,
            ]);
            $message = 'Cảm ơn bạn đã gửi đánh giá cho sản phẩm!';
        }

        return back()->with('success', $message);
    }
}
