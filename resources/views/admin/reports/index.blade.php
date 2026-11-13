@extends('layouts.admin')

@section('title', 'Thống kê doanh thu & tồn kho - Quản trị')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Thống kê doanh thu &amp; tồn kho</h1>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Bộ lọc --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="period" class="form-label small fw-semibold">Kỳ thống kê</label>
                    <select id="period" name="period" class="form-select form-select-sm">
                        <option value="daily" {{ $filters['period'] === 'daily' ? 'selected' : '' }}>Theo ngày</option>
                        <option value="monthly" {{ $filters['period'] === 'monthly' ? 'selected' : '' }}>Theo tháng</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="from_date" class="form-label small fw-semibold">Từ ngày</label>
                    <input id="from_date" type="date" name="from_date" value="{{ $filters['from_date'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="to_date" class="form-label small fw-semibold">Đến ngày</label>
                    <input id="to_date" type="date" name="to_date" value="{{ $filters['to_date'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="low_stock_threshold" class="form-label small fw-semibold">Ngưỡng sắp hết hàng (&le;)</label>
                    <div class="input-group input-group-sm">
                        <input id="low_stock_threshold"
                               type="number"
                               name="low_stock_threshold"
                               value="{{ $filters['low_stock_threshold'] }}"
                               min="0"
                               max="1000"
                               class="form-control">
                        <button type="submit" class="btn btn-primary">Xem</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Thẻ tổng quan --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Tổng doanh thu (đơn hoàn thành)</div>
                    <div class="h4 fw-bold text-primary mt-1 mb-0">
                        {{ number_format($summary['tong_doanh_thu'], 0, ',', '.') }} đ
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Số đơn hoàn thành</div>
                    <div class="h4 fw-bold text-success mt-1 mb-0">
                        {{ number_format($summary['tong_don_hoan_thanh']) }} đơn
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Sản phẩm sắp hết hàng (&le; {{ $filters['low_stock_threshold'] }})</div>
                    <div class="h4 fw-bold text-danger mt-1 mb-0">
                        {{ $summary['so_sp_sap_het'] }} sản phẩm
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Biểu đồ doanh thu --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">
            <i class="bi bi-graph-up me-2 text-primary"></i>
            Biểu đồ doanh thu {{ $filters['period'] === 'monthly' ? 'theo tháng' : 'theo ngày' }}
        </div>
        <div class="card-body">
            @if ($revenueSeries->isNotEmpty())
                <div style="height: 320px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            @else
                <div class="text-muted text-center py-4">Chưa có dữ liệu đơn hoàn thành trong khoảng thời gian này.</div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Top sản phẩm bán chạy --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-trophy me-2 text-warning"></i>Sản phẩm bán chạy
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th>Tên sản phẩm</th>
                                <th class="text-end">Đã bán</th>
                                <th class="text-end">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProducts as $item)
                                <tr>
                                    <td class="text-muted">{{ $item->MaSKU }}</td>
                                    <td class="fw-semibold">{{ $item->TenSanPham }}</td>
                                    <td class="text-end">{{ number_format((int) $item->tong_so_luong) }}</td>
                                    <td class="text-end text-primary fw-semibold">
                                        {{ number_format((float) $item->tong_doanh_thu, 0, ',', '.') }} đ
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Chưa có dữ liệu bán hàng.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Cảnh báo sắp hết hàng --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Cảnh báo sắp hết hàng
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th>Sản phẩm</th>
                                <th>Danh mục</th>
                                <th class="text-end">Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStockProducts as $product)
                                <tr>
                                    <td class="text-muted">{{ $product->MaSKU }}</td>
                                    <td class="fw-semibold">{{ $product->TenSanPham }}</td>
                                    <td>{{ $product->TenDanhMuc }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ (int) $product->SoLuongTon === 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            {{ (int) $product->SoLuongTon }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Không có sản phẩm nào dưới ngưỡng cảnh báo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($revenueSeries->isNotEmpty())
        {{-- Chart.js 4.4 (MIT License) --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            const chartLabels = @json($revenueSeries->pluck('nhan_thoi_gian')->values());
            const chartData = @json($revenueSeries->pluck('doanh_thu')->map(fn ($v) => (float) $v)->values());

            const ctx = document.getElementById('revenueChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Doanh thu (VNĐ)',
                            data: chartData,
                            backgroundColor: 'rgba(13, 110, 253, 0.65)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        </script>
    @endif
@endpush
