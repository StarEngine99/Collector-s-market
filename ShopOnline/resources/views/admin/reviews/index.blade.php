@extends('layouts.admin')

@section('title', 'Đánh giá sản phẩm - Quản trị')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Đánh giá sản phẩm</h1>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Số sao</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Ngày gửi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $review->TenSanPham }}</div>
                                <div class="small text-muted">SKU: {{ $review->MaSKU }}</div>
                            </td>
                            <td>
                                <div>{{ $review->HoTen ?? 'Tài khoản đã xóa' }}</div>
                                @if ($review->Email)
                                    <div class="small text-muted">{{ $review->Email }}</div>
                                @endif
                            </td>
                            <td class="text-center text-warning text-nowrap">
                                @for ($star = 0; $star < (int) $review->SoSao; $star++)
                                    <i class="bi bi-star-fill" aria-hidden="true"></i>
                                @endfor
                                <span class="visually-hidden">{{ $review->SoSao }} trên 5 sao</span>
                            </td>
                            <td class="small" style="min-width: 240px; white-space: pre-line;">{{ $review->NoiDung }}</td>
                            <td>
                                @if ($review->TrangThai === 'published')
                                    <span class="badge bg-success">Đã đăng</span>
                                @elseif ($review->TrangThai === 'pending')
                                    <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                @else
                                    <span class="badge bg-secondary">{{ $review->TrangThai }}</span>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $review->NgayTao ? \Illuminate\Support\Carbon::parse($review->NgayTao)->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có đánh giá sản phẩm.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())
            <div class="card-footer bg-white">{{ $reviews->links() }}</div>
        @endif
    </div>
@endsection
