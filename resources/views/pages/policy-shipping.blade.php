@extends('layouts.app')

@section('title', 'Chính sách vận chuyển - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 fw-bold mb-3">Chính sách vận chuyển</h1>
            <p class="text-muted">Thông tin thời gian xử lý và giao nhận đơn hàng trên toàn quốc.</p>
            <hr class="my-4">
            <h2 class="h5 fw-semibold">1. Thời gian xử lý đơn hàng</h2>
            <p class="text-muted">
                Đơn hàng được xác nhận và đóng gói trong vòng <strong>24 giờ</strong> làm việc (trừ Chủ nhật và ngày lễ).
            </p>

            <h2 class="h5 fw-semibold mt-4">2. Thời gian giao hàng dự kiến</h2>
            <ul class="text-muted mb-0">
                <li>Nội tỉnh / khu vực Đồng bằng sông Cửu Long: 1 – 3 ngày làm việc.</li>
                <li>Các tỉnh thành khác trên toàn quốc: 3 – 5 ngày làm việc.</li>
            </ul>
        </div>
    </div>
@endsection
