@extends('layouts.admin')

@section('title', 'Quản lý khách hàng | Quản trị Collector\'s Market')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Quản lý khách hàng (B4)</h1>
        <p class="text-muted mb-0">Tra cứu khách hàng, xem lịch sử mua sắm và khóa/mở khóa tài khoản.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Bộ lọc tìm kiếm & trạng thái --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Tìm kiếm khách hàng</label>
                <input
                    type="text"
                    name="keyword"
                    value="{{ $keyword }}"
                    class="form-control"
                    placeholder="Họ tên, email hoặc số điện thoại..."
                >
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Trạng thái tài khoản</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="active" @selected($status === 'active')>Đang hoạt động (Active)</option>
                    <option value="locked" @selected($status === 'locked')>Đã bị khóa (Locked)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Lọc</button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
            </div>
        </form>
    </div>
</div>

{{-- Bảng danh sách khách hàng --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Họ tên & Email</th>
                        <th>Số điện thoại</th>
                        <th class="text-center">Số đơn</th>
                        <th class="text-end">Tổng chi tiêu</th>
                        <th class="text-center">Trạng thái</th>
                        <th>Ngày đăng ký</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="ps-4 font-monospace">#{{ $customer->MaNguoiDung }}</td>
                            <td>
                                <div class="fw-semibold">{{ $customer->HoTen }}</div>
                                <small class="text-muted">{{ $customer->Email }}</small>
                            </td>
                            <td>{{ $customer->SoDienThoai ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ (int) $customer->TongSoDon }}</span>
                            </td>
                            <td class="text-end fw-semibold text-danger">
                                {{ number_format((float) $customer->TongChiTieu, 0, ',', '.') }} đ
                            </td>
                            <td class="text-center">
                                @if($customer->TrangThai === 'active')
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger">Đã khóa</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($customer->NgayTao)->format('d/m/Y') }}</td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.customers.show', $customer->MaNguoiDung) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Lịch sử mua
                                    </a>

                                    <form action="{{ route('admin.customers.status', $customer->MaNguoiDung) }}"
                                          method="POST"
                                          onsubmit="return confirm('Xác nhận thay đổi trạng thái tài khoản {{ $customer->Email }}?');">
                                        @csrf
                                        @method('PATCH')
                                        @if($customer->TrangThai === 'active')
                                            <input type="hidden" name="TrangThai" value="locked">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Khóa</button>
                                        @else
                                            <input type="hidden" name="TrangThai" value="active">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Mở khóa</button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                Không tìm thấy khách hàng nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($customers->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
