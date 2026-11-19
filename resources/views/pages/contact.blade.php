@extends('layouts.app')

@section('title', 'Liên hệ - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-3">Thông tin liên hệ</h1>
                    <p class="text-muted small">
                        Nếu bạn có câu hỏi về sản phẩm, đơn hàng hoặc chính sách, hãy gửi tin nhắn cho chúng tôi qua biểu mẫu bên cạnh.
                    </p>
                    <ul class="list-unstyled d-flex flex-column gap-3 mt-4 mb-0">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-geo-alt text-primary fs-5"></i>
                            <div>
                                <div class="fw-semibold">Địa chỉ</div>
                                <div class="text-muted small">Trường Đại học Đồng Tháp, TP. Cao Lãnh, Đồng Tháp</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-clock text-primary fs-5"></i>
                            <div>
                                <div class="fw-semibold">Giờ hỗ trợ</div>
                                <div class="text-muted small">Thứ 2 – Thứ 7: 08:00 – 17:30</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3">Gửi yêu cầu hỗ trợ</h2>

                    <form method="POST" action="{{ route('pages.contact.submit') }}" novalidate>
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="ho_ten" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                <input id="ho_ten"
                                       type="text"
                                       name="ho_ten"
                                       value="{{ old('ho_ten') }}"
                                       class="form-control @error('ho_ten') is-invalid @enderror"
                                       minlength="2"
                                       maxlength="150"
                                       required>
                                @error('ho_ten')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror"
                                       maxlength="190"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="so_dien_thoai" class="form-label fw-semibold">Số điện thoại</label>
                                <input id="so_dien_thoai"
                                       type="text"
                                       name="so_dien_thoai"
                                       value="{{ old('so_dien_thoai') }}"
                                       class="form-control @error('so_dien_thoai') is-invalid @enderror"
                                       maxlength="30">
                                @error('so_dien_thoai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="chu_de" class="form-label fw-semibold">Chủ đề <span class="text-danger">*</span></label>
                                <input id="chu_de"
                                       type="text"
                                       name="chu_de"
                                       value="{{ old('chu_de') }}"
                                       class="form-control @error('chu_de') is-invalid @enderror"
                                       minlength="3"
                                       maxlength="200"
                                       required>
                                @error('chu_de')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="noi_dung" class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                                <textarea id="noi_dung"
                                          name="noi_dung"
                                          rows="5"
                                          class="form-control @error('noi_dung') is-invalid @enderror"
                                          minlength="10"
                                          maxlength="3000"
                                          required>{{ old('noi_dung') }}</textarea>
                                @error('noi_dung')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-send me-1"></i>Gửi liên hệ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
