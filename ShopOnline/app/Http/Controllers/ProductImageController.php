<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProductImageController extends Controller
{
    public function show(int $id): Response
    {
        $image = DB::table('product_images')
            ->select(['DuLieuAnh', 'LoaiNoiDung'])
            ->where('MaHinhAnh', $id)
            ->first();

        if ($image === null || !is_string($image->DuLieuAnh) || $image->DuLieuAnh === '') {
            abort(404);
        }

        $contentType = match ($image->LoaiNoiDung) {
            'image/jpeg', 'image/png', 'image/webp' => $image->LoaiNoiDung,
            default => abort(404),
        };

        return response($image->DuLieuAnh)
            ->header('Content-Type', $contentType)
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
