@extends('layouts.admin')

@section('title', 'Quản lý mã giảm giá - Quản trị')

@section('content')
    <ul class="nav nav-pills mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link active fw-semibold" href="{{ route('admin.coupons.index') }}">
                <i class="bi bi-ticket-perforated me-1"></i>Mã giảm giá (Coupons)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-white border text-dark" href="{{ route('admin.promotions.index') }}">
                <i class="bi bi-tags me-1"></i>Khuyến mãi theo sản phẩm
            </a>
        </li>
    </ul>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <h1 class="h4 fw-bold mb-0">Quản lý mã giảm giá</h1>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Thêm mã giảm giá
        </a>
    </div>

    <form method="GET" action="{{ route('admin.coupons.index') }}" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="searchCode" class="form-label small fw-semibold">Mã ưu đãi</label>
                    <input id="searchCode"
                           type="text"
                           name="q"
                           value="{{ $keyword }}"
                           class="form-control form-control-sm"
                           placeholder="Tìm theo mã ưu đãi...">
                </div>
                <div class="col-6 col-md-3">
                    <label for="filterType" class="form-label small fw-semibold">Loại giảm giá</label>
                    <select id="filterType" name="type" class="form-select form-select-sm">
                        <option value="">Tất cả loại</option>
                        <option value="percent" {{ $type === 'percent' ? 'selected' : '' }}>Theo phần trăm (%)</option>
                        <option value="fixed" {{ $type === 'fixed' ? 'selected' : '' }}>Số tiền cố định (đ)</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="filterStatus" class="form-label small fw-semibold">Trạng thái</label>
                    <select id="filterStatus" name="status" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Đang kích hoạt (active)</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Ngừng kích hoạt (inactive)</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary btn-sm flex-grow-1">Lọc</button>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary btn-sm">Đặt lại</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Mã ưu đãi</th>
                        <th>Mức giảm</th>
                        <th>Đơn tối thiểu</th>
                        <th>Lượt dùng</th>
                        <th>Thời hạn hiệu lực</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->MaPhieuGiamGia }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fs-6">
                                    {{ $coupon->MaUuDai }}
                                </span>
                            </td>
                            <td class="fw-semibold text-danger">
                                @if ($coupon->LoaiGiamGia === 'percent')
                                    -{{ rtrim(rtrim(number_format((float) $coupon->GiaTriGiam, 2, ',', '.'), '0'), ',') }}%
                                @else
                                    -{{ number_format((float) $coupon->GiaTriGiam, 0, ',', '.') }} đ
                                @endif
                            </td>
                            <td class="small">
                                @if ((float) $coupon->DonHangToiThieu > 0)
                                    {{ number_format((float) $coupon->DonHangToiThieu, 0, ',', '.') }} đ
                                @else
                                    <span class="text-muted">Không giới hạn</span>
                                @endif
                            </td>
                            <td class="small">
                                <div>
                                    <strong>{{ (int) $coupon->SoLanDaSuDung }}</strong> /
                                    {{ $coupon->SoLanSuDungToiDa ?? '∞' }}
                                </div>
                                @if ($coupon->SoLanSuDungToiDaMoiNguoi !== null)
                                    <div class="text-muted">Tối đa {{ (int) $coupon->SoLanSuDungToiDaMoiNguoi }}/khách</div>
                                @endif
                            </td>
                            <td class="small text-muted">
                                <div>Từ: {{ $coupon->BatDauLuc }}</div>
                                <div>Đến: {{ $coupon->KetThucLuc }}</div>
                            </td>
                            <td>
                                @if ($coupon->TrangThai === 'active')
                                    <span class="badge bg-success">Kích hoạt</span>
                                @else
                                    <span class="badge bg-secondary">Ngừng</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.coupons.edit', $coupon->MaPhieuGiamGia) }}"
                                       class="btn btn-outline-primary btn-sm"
                                       title="Chỉnh sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.coupons.destroy', $coupon->MaPhieuGiamGia) }}"
                                          onsubmit="return confirm('Bạn chắc chắn muốn xóa mã giảm giá này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Chưa có mã giảm giá nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($coupons->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
@endsection

