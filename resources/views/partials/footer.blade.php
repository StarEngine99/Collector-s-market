<footer class="bg-dark text-light pt-5 pb-3 mt-auto">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <h5 class="fw-bold text-white mb-3">
                    <i class="bi bi-stars text-primary me-1"></i> Chợ Figure &amp; Thẻ
                </h5>
                <p class="text-white-50 small mb-2">
                    Cửa hàng chuyên cung cấp mô hình sưu tầm, thẻ bài giao dịch và phụ kiện bảo quản chính hãng.
                </p>
                <p class="text-white-50 small mb-0">
                    <i class="bi bi-geo-alt me-1"></i> Trường Đại học Đồng Tháp — Đồ án môn Phát triển phần mềm mã nguồn mở
                </p>
            </div>

            <div class="col-6 col-md-4">
                <h6 class="fw-semibold text-white mb-3">Danh mục sản phẩm</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li>
                        <a href="{{ Route::has('categories.show') ? route('categories.show', 'mo-hinh') : '#' }}"
                           class="text-white-50 text-decoration-none footer-link">Mô hình (Figure)</a>
                    </li>
                    <li>
                        <a href="{{ Route::has('categories.show') ? route('categories.show', 'the-bai') : '#' }}"
                           class="text-white-50 text-decoration-none footer-link">Thẻ bài (Trading Card)</a>
                    </li>
                    <li>
                        <a href="{{ Route::has('categories.show') ? route('categories.show', 'phu-kien') : '#' }}"
                           class="text-white-50 text-decoration-none footer-link">Phụ kiện bảo quản</a>
                    </li>
                </ul>
            </div>

            <div class="col-6 col-md-4">
                <h6 class="fw-semibold text-white mb-3">Thông tin &amp; Chính sách</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li>
                        <a href="{{ Route::has('pages.about') ? route('pages.about') : '#' }}"
                           class="text-white-50 text-decoration-none footer-link">Giới thiệu</a>
                    </li>
                    <li>
                        <a href="{{ Route::has('pages.policy.return') ? route('pages.policy.return') : '#' }}"
                           class="text-white-50 text-decoration-none footer-link">Chính sách đổi trả</a>
                    </li>
                    <li>
                        <a href="{{ Route::has('pages.policy.shipping') ? route('pages.policy.shipping') : '#' }}"
                           class="text-white-50 text-decoration-none footer-link">Chính sách vận chuyển</a>
                    </li>
                    <li>
                        <a href="{{ Route::has('pages.policy.privacy') ? route('pages.policy.privacy') : '#' }}"
                           class="text-white-50 text-decoration-none footer-link">Chính sách bảo mật</a>
                    </li>
                    <li>
                        <a href="{{ Route::has('pages.contact') ? route('pages.contact') : '#' }}"
                           class="text-white-50 text-decoration-none footer-link">Liên hệ hỗ trợ</a>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-white-50 gap-2">
            <span>&copy; {{ date('Y') }} Chợ Figure &amp; Thẻ. Mã nguồn mở (MIT).</span>
            <span>Xây dựng bằng Laravel &amp; Bootstrap 5</span>
        </div>
    </div>
</footer>
