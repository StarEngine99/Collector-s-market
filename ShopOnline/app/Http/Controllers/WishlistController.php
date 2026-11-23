<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    /**
     * Thêm / Bỏ yêu thích sản phẩm.
     */
    public function toggle(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để lưu sản phẩm yêu thích!',
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để lưu sản phẩm yêu thích.');
        }

        $userId = (int) $user->getKey();
        $exists = DB::table('wishlists')
            ->where('MaNguoiDung', $userId)
            ->where('MaSanPham', $productId)
            ->first();

        if ($exists) {
            DB::table('wishlists')
                ->where('MaNguoiDung', $userId)
                ->where('MaSanPham', $productId)
                ->delete();
            $favorited = false;
            $msg = 'Đã bỏ sản phẩm khỏi danh sách yêu thích.';
        } else {
            DB::table('wishlists')->insert([
                'MaNguoiDung' => $userId,
                'MaSanPham' => $productId,
                'NgayTao' => Carbon::now(),
            ]);
            $favorited = true;
            $msg = 'Đã thêm sản phẩm vào danh sách yêu thích!';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'favorited' => $favorited,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
