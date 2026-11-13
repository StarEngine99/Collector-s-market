@extends('layouts.admin')

@section('title', 'Quản lý banner trang chủ - Quản trị')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <h1 class="h4 fw-bold mb-0">Quản lý banner trang chủ</h1>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Thêm banner
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Thứ tự hiển thị</th>
                        <th>Hiệu lực</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($banners as $banner)
                        @php
                            $imgUrl = str_starts_with($banner->DuongDanHinhAnh, 'http')
                                ? $banner->DuongDanHinhAnh
                                : asset($banner->DuongDanHinhAnh);
                        @endphp
                        <tr>
                            <td>{{ $banner->MaBanner }}</td>
                            <td>
                                <img src="{{ $imgUrl }}"
                                     alt="{{ $banner->TieuDe }}"
                                     class="rounded border"
                                     style="width: 96px; height: 48px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $banner->TieuDe }}</div>
                                @if (!empty($banner->DuongDanDich))
                                    <div class="small text-muted">{{ $banner->DuongDanDich }}</div>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.banners.order', $banner->MaBanner) }}" class="d-inline-flex gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number"
                                           name="ThuTuSapXep"
                                           value="{{ $banner->ThuTuSapXep }}"
                                           min="0"
                                           max="9999"
                                           class="form-control form-control-sm text-center"
                                           style="width: 75px;"
                                           aria-label="Thứ tự banner {{ $banner->MaBanner }}">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Lưu thứ tự">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="small text-muted">
                                {{ $banner->BatDauLuc ?? 'Luôn' }} → {{ $banner->KetThucLuc ?? 'Không giới hạn' }}
                            </td>
                            <td>
                                @if ($banner->TrangThai === 'active')
                                    <span class="badge bg-success">Hiển thị</span>
                                @else
                                    <span class="badge bg-secondary">Đang tắt</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.banners.edit', $banner->MaBanner) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.banners.destroy', $banner->MaBanner) }}"
                                          onsubmit="return confirm('Bạn chắc chắn muốn xóa banner này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có banner nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($banners->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $banners->links() }}
            </div>
        @endif
    </div>
@endsection
