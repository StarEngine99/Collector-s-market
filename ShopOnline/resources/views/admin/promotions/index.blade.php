@extends('layouts.admin')

@section('title', 'Quản lý khuyến mãi theo sản phẩm - Quản trị')

@section('content')
    <ul class="nav nav-pills mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link bg-white border text-dark" href="{{ route('admin.coupons.index') }}">
                <i class="bi bi-ticket-perforated me-1"></i>Mã giảm giá (Coupons)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-semibold" href="{{ route('admin.promotions.index') }}">
                <i class="bi bi-tags me-1"></i>Khuyến mãi theo sản phẩm
            </a>
        </li>
    </ul>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <h1 class="h4 fw-bold mb-0">Quản lý khuyến mãi theo sản phẩm</h1>
        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Thêm chương trình khuyến mãi
        </a>
    </div>

    <form method="GET" action="{{ route('admin.promotions.index') }}" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="searchPromotion" class="form-label small fw-semibold">Tên chương trình</label>
                    <input id="searchPromotion"
                           type="text"
                           name="q"
                           value="{{ $keyword }}"
                           class="form-control form-control-sm"
                           placeholder="Tìm theo tên khuyến mãi...">
                </div>
                <div class="col-12 col-md-4">
                    <label for="filterStatus" class="form-label small fw-semibold">Trạng thái</label>
                    <select id="filterStatus" name="status" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Lên lịch (scheduled)</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Đang chạy (active)</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Ngừng kích hoạt (inactive)</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary btn-sm flex-grow-1">Lọc</button>
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary btn-sm">Đặt lại</a>
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
                        <th>Tên chương trình</th>
                        <th>Mức giảm</th>
                        <th>Số SP áp dụng</th>
                        <th>Thời gian hiệu lực</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($promotions as $promotion)
                        <tr>
                            <td>{{ $promotion->MaKhuyenMai }}</td>
                            <td>
                                <div class="fw-semibold">{{ $promotion->TenKhuyenMai }}</div>
                                @if (!empty($promotion->MoTa))
                                    <div class="small text-muted text-truncate" style="max-width: 320px;">
                                        {{ $promotion->MoTa }}
                                    </div>
                                @endif
                            </td>
                            <td class="fw-semibold text-danger">
                                @if ($promotion->LoaiGiamGia === 'percent')
                                    -{{ rtrim(rtrim(number_format((float) $promotion->GiaTriGiam, 2, ',', '.'), '0'), ',') }}%
                                @else
                                    -{{ number_format((float) $promotion->GiaTriGiam, 0, ',', '.') }} đ
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                    {{ (int) $promotion->so_san_pham }} sản phẩm
                                </span>
                            </td>
                            <td class="small text-muted">
                                <div>Từ: {{ $promotion->BatDauLuc }}</div>
                                <div>Đến: {{ $promotion->KetThucLuc }}</div>
                            </td>
                            <td>
                                @if ($promotion->TrangThai === 'active')
                                    <span class="badge bg-success">Đang chạy</span>
                                @elseif ($promotion->TrangThai === 'scheduled')
                                    <span class="badge bg-warning text-dark">Lên lịch</span>
                                @else
                                    <span class="badge bg-secondary">Ngừng</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.promotions.edit', $promotion->MaKhuyenMai) }}"
                                       class="btn btn-outline-primary btn-sm"
                                       title="Chỉnh sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.promotions.destroy', $promotion->MaKhuyenMai) }}"
                                          onsubmit="return confirm('Bạn chắc chắn muốn xóa chương trình khuyến mãi này?');">
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
                            <td colspan="7" class="text-center text-muted py-4">Chưa có chương trình khuyến mãi nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($promotions->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $promotions->links() }}
            </div>
        @endif
    </div>
@endsection
