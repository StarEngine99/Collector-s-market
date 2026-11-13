@extends('layouts.app')

@section('title', 'Chính sách bảo mật - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 fw-bold mb-3">Chính sách bảo mật</h1>
            <p class="text-muted">Cam kết bảo vệ thông tin cá nhân của khách hàng khi mua sắm tại website.</p>
            <hr class="my-4">
            <h2 class="h5 fw-semibold">1. Thu thập và sử dụng thông tin</h2>
            <p class="text-muted">
                Thông tin họ tên, email, số điện thoại và địa chỉ giao hàng chỉ được sử dụng cho mục đích xác nhận đơn hàng, giao hàng và hỗ trợ khách hàng.
            </p>

            <h2 class="h5 fw-semibold mt-4">2. Bảo mật dữ liệu</h2>
            <p class="text-muted mb-0">
                Mật khẩu tài khoản được băm (hash) an toàn trên máy chủ. Chúng tôi không chia sẻ hoặc bán thông tin khách hàng cho bên thứ ba ngoài phạm vi đơn vị vận chuyển phục vụ đơn hàng.
            </p>
        </div>
    </div>
@endsection
