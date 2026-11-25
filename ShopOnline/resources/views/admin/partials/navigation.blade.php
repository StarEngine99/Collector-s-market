<div class="px-3 py-2 text-uppercase small fw-semibold admin-nav-heading">Tổng quan</div>
@can('users.manage')
    <a href="{{ route('admin.dashboard') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2-fill me-2"></i> Dashboard
    </a>
@endcan

<div class="px-3 py-2 mt-3 text-uppercase small fw-semibold admin-nav-heading">Cửa hàng</div>
@can('orders.view')
    <a href="{{ route('admin.orders.index') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
        <i class="bi bi-receipt me-2"></i> Đơn hàng
    </a>
@endcan
@can('customers.view')
    <a href="{{ route('admin.customers.index') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
        <i class="bi bi-people me-2"></i> Khách hàng
    </a>
@endcan
@can('products.view')
    <a href="{{ route('admin.products.index') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
        <i class="bi bi-box-seam me-2"></i> Sản phẩm
    </a>
@endcan
@can('coupons.manage')
    <a href="{{ route('admin.promotions.index') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
        <i class="bi bi-megaphone me-2"></i> Khuyến mãi
    </a>
    <a href="{{ route('admin.coupons.index') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
        <i class="bi bi-ticket-perforated me-2"></i> Mã giảm giá
    </a>
@endcan

<div class="px-3 py-2 mt-3 text-uppercase small fw-semibold admin-nav-heading">Nội dung &amp; báo cáo</div>
@can('content.manage')
    <a href="{{ route('admin.posts.index') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-text me-2"></i> Bài viết
    </a>
    <a href="{{ route('admin.banners.index') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
        <i class="bi bi-images me-2"></i> Banner trang chủ
    </a>
@endcan
@can('users.manage')
    <a href="{{ route('admin.reviews.index') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
        <i class="bi bi-star-half me-2"></i> Đánh giá sản phẩm
    </a>
@endcan
@can('reports.view')
    <a href="{{ route('admin.reports.index') }}"
       class="list-group-item list-group-item-action border-0 admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <i class="bi bi-bar-chart-line me-2"></i> Thống kê doanh thu
    </a>
@endcan
