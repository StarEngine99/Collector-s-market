@extends('layouts.app')

@section('title', 'Chính sách đổi trả - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 fw-bold mb-3">Chính sách đổi trả</h1>
            <p class="text-muted">Chúng tôi hỗ trợ đổi trả minh bạch để đảm bảo quyền lợi cho người sưu tầm.</p>
            <hr class="my-4">
            <h2 class="h5 fw-semibold">1. Điều kiện áp dụng đổi trả</h2>
            <ul class="text-muted">
                <li>Sản phẩm giao sai mẫu, sai số lượng hoặc bị hư hỏng trong quá trình vận chuyển.</li>
                <li>Yêu cầu đổi trả được gửi trong vòng <strong>7 ngày</strong> kể từ ngày nhận hàng.</li>
                <li>Sản phẩm còn nguyên tem niêm phong, phụ kiện đi kèm và chưa qua sử dụng.</li>
            </ul>

            <h2 class="h5 fw-semibold mt-4">2. Trường hợp không hỗ trợ đổi trả</h2>
            <ul class="text-muted mb-0">
                <li>Sản phẩm thẻ bài đã bóc seal/booster pack hoặc mô hình đã tháo seal hộp.</li>
                <li>Hư hỏng do lỗi bảo quản hoặc tác động vật lý từ phía người mua sau khi nhận hàng.</li>
            </ul>
        </div>
    </div>
@endsection
