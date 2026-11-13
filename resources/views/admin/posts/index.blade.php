@extends('layouts.admin')

@section('title', 'Quản lý bài viết - Quản trị')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <h1 class="h4 fw-bold mb-0">Quản lý bài viết</h1>
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Thêm bài viết
        </a>
    </div>

    <form method="GET" action="{{ route('admin.posts.index') }}" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="searchTitle" class="form-label small fw-semibold">Tiêu đề</label>
                    <input id="searchTitle" type="text" name="q" value="{{ $keyword }}" class="form-control form-control-sm" placeholder="Tìm theo tiêu đề...">
                </div>
                <div class="col-12 col-md-4">
                    <label for="filterStatus" class="form-label small fw-semibold">Trạng thái</label>
                    <select id="filterStatus" name="status" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Bản nháp (draft)</option>
                        <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Đã xuất bản (published)</option>
                        <option value="hidden" {{ $status === 'hidden' ? 'selected' : '' }}>Đang ẩn (hidden)</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary btn-sm flex-grow-1">Lọc</button>
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary btn-sm">Đặt lại</a>
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
                        <th>Tiêu đề</th>
                        <th>Đường dẫn</th>
                        <th>Tác giả</th>
                        <th>Trạng thái</th>
                        <th>Ngày đăng</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>{{ $post->MaBaiViet }}</td>
                            <td class="fw-semibold">{{ $post->TieuDe }}</td>
                            <td class="small text-muted">{{ $post->DuongDan }}</td>
                            <td class="small">{{ $post->TenTacGia ?? '—' }}</td>
                            <td>
                                @if ($post->TrangThai === 'published')
                                    <span class="badge bg-success">Đã xuất bản</span>
                                @elseif ($post->TrangThai === 'draft')
                                    <span class="badge bg-secondary">Bản nháp</span>
                                @else
                                    <span class="badge bg-warning text-dark">Đang ẩn</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $post->NgayDang ?? '—' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.posts.edit', $post->MaBaiViet) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.posts.destroy', $post->MaBaiViet) }}"
                                          onsubmit="return confirm('Bạn chắc chắn muốn xóa bài viết này?');">
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
                            <td colspan="7" class="text-center text-muted py-4">Chưa có bài viết nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($posts->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
