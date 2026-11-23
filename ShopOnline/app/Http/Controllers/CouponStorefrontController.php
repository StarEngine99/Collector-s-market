<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CouponStorefrontController extends Controller
{
    /**
     * Hiển thị danh sách phiếu giảm giá công khai.
     */
    public function index(Request $request): View
    {
        // "Ngừng kích hoạt" mới bị ẩn. Còn thời gian sẽ do mã tự kiểm tra khi áp dụng.
        $coupons = DB::table('coupons')
            ->where('TrangThai', 'active')
            ->orderByDesc('GiaTriGiam')
            ->orderByDesc('MaPhieuGiamGia')
            ->get();

        $userId = $request->user()?->getKey();
        $savedCouponIds = [];
        if ($userId) {
            $savedCouponIds = DB::table('user_coupons')
                ->where('MaNguoiDung', $userId)
                ->pluck('MaPhieuGiamGia')
                ->all();
        }

        return view('coupons.index', [
            'coupons' => $coupons,
            'savedCouponIds' => $savedCouponIds,
        ]);
    }

    /**
     * Lưu phiếu giảm giá vào ví người dùng.
     */
    public function claim(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu phiếu!'], 401);
            }
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để lưu phiếu giảm giá.');
        }

        $coupon = DB::table('coupons')->where('MaPhieuGiamGia', $id)->first();
        if (!$coupon) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Phiếu giảm giá không tồn tại.'], 404);
            }
            return back()->with('error', 'Phiếu giảm giá không tồn tại.');
        }

        DB::table('user_coupons')->updateOrInsert([
            'MaNguoiDung' => $user->getKey(),
            'MaPhieuGiamGia' => $id,
        ], [
            'NgayLuu' => Carbon::now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã lưu phiếu vào ví của bạn thành công!']);
        }

        return back()->with('success', 'Đã lưu phiếu giảm giá "' . $coupon->MaUuDai . '" vào ví của bạn!');
    }
}
