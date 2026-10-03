<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siêu Thị Điện Máy HC - Điều Hòa Chính Hãng</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .bg-hc { background-color: #d71921 !important; }
        .text-hc { color: #d71921 !important; }
        .btn-hc { background-color: #d71921; color: #fff; border: none; }
        .btn-hc:hover { background-color: #b51219; color: #fff; }
        .w-fit-content { width: max-content; }
        
        /* Fix triệt để menu dropdown bị che hoặc không ấn được */
        header { overflow: visible !important; z-index: 9999 !important; }
        .dropdown-menu { z-index: 10000 !important; }
        .admin-shell { --admin-ink: #172033; --admin-muted: #7b879b; --admin-line: #e7ebf2; background: #f5f7fb; min-height: 100vh; color: var(--admin-ink); }
        .admin-shell .admin-sidebar { width: 248px; background: #172033; color: #fff; position: fixed; inset: 0 auto 0 0; z-index: 1030; display: flex; flex-direction: column; }
        .admin-shell .admin-brand { height: 76px; padding: 0 24px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,.1); color: #fff; text-decoration: none; font-weight: 800; font-size: 1.1rem; }
        .admin-shell .admin-brand-mark { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 10px; background: #e21b23; color: #fff; }
        .admin-shell .admin-nav { padding: 22px 13px; flex: 1; }
        .admin-shell .admin-nav-label { color: #7f8ba0; text-transform: uppercase; letter-spacing: .12em; font-size: .66rem; font-weight: 800; padding: 0 12px 10px; }
        .admin-shell .admin-nav a { color: #b9c2d1; border-radius: 10px; display: flex; align-items: center; gap: 12px; padding: 11px 12px; margin: 3px 0; text-decoration: none; font-size: .9rem; transition: .18s ease; }
        .admin-shell .admin-nav a i { width: 20px; text-align: center; color: #8290a8; }
        .admin-shell .admin-nav a:hover, .admin-shell .admin-nav a.active { background: rgba(255,255,255,.1); color: #fff; }
        .admin-shell .admin-nav a.active { box-shadow: inset 3px 0 #e21b23; }
        .admin-shell .admin-nav a.active i { color: #ff6c72; }
        .admin-shell .admin-user { border-top: 1px solid rgba(255,255,255,.1); padding: 16px 18px; color: #c5ccda; font-size: .82rem; }
        .admin-shell .admin-user form { margin-top: 10px; }
        .admin-shell .admin-user button { color: #ff9da1; background: transparent; border: 0; padding: 0; font-size: .8rem; }
        .admin-shell .admin-main { margin-left: 248px; min-height: 100vh; }
        .admin-shell .admin-topbar { height: 76px; background: #fff; border-bottom: 1px solid var(--admin-line); display: flex; align-items: center; justify-content: space-between; padding: 0 34px; }
        .admin-shell .admin-topbar-title { font-weight: 800; font-size: 1.05rem; }
        .admin-shell .admin-topbar-meta { color: var(--admin-muted); font-size: .8rem; }
        .admin-shell .admin-content { padding: 30px 34px; }
        .admin-shell .admin-mobile-toggle { display: none; }
        @media (max-width: 991.98px) { .admin-shell .admin-sidebar { transform: translateX(-100%); transition: transform .2s ease; } .admin-shell.sidebar-open .admin-sidebar { transform: translateX(0); } .admin-shell .admin-main { margin-left: 0; } .admin-shell .admin-mobile-toggle { display: inline-grid; place-items: center; border: 0; background: transparent; font-size: 1.2rem; color: var(--admin-ink); } .admin-shell .admin-topbar { padding: 0 20px; } .admin-shell .admin-content { padding: 22px 18px; } }
    </style>
</head>
@php($isAdminArea = request()->is('admin/*') || request()->routeIs('air_conditioners.*'))
@php($isChatPage = request()->routeIs('chat.*', 'admin.chat.*'))
<body class="{{ $isAdminArea ? 'admin-shell' : 'bg-light d-flex flex-column min-vh-100' }} {{ $isChatPage ? 'chat-layout' : '' }}">

    @if($isAdminArea)
        <div class="admin-sidebar">
            <a class="admin-brand" href="{{ route('air_conditioners.index') }}"><span class="admin-brand-mark"><i class="fa-solid fa-snowflake"></i></span><span>HC <small class="fw-normal">{{ Auth::user()->role === 'admin' ? 'ADMIN' : 'STAFF' }}</small></span></a>
            <nav class="admin-nav">
                <div class="admin-nav-label">Workspace</div>
                <a href="{{ route('air_conditioners.index') }}" class="{{ request()->routeIs('air_conditioners.*') ? 'active' : '' }}"><i class="fa-solid fa-boxes-stacked"></i><span>Sản phẩm</span></a>
                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><i class="fa-solid fa-bag-shopping"></i><span>Đơn hàng</span></a>
                <a href="{{ route('admin.chat.index') }}" class="{{ request()->routeIs('admin.chat.*') ? 'active' : '' }}"><i class="fa-solid fa-comments"></i><span>Tin nhắn</span></a>
                <a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"><i class="fa-solid fa-ticket"></i><span>Mã giảm giá</span></a>
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"><i class="fa-solid fa-users"></i><span>Khách hàng</span></a>
                    <a href="{{ route('admin.finance.index') }}" class="{{ request()->routeIs('admin.finance.*') ? 'active' : '' }}"><i class="fa-solid fa-wallet"></i><span>Tài chính</span></a>
                    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><i class="fa-solid fa-chart-line"></i><span>Báo cáo</span></a>
                    <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><i class="fa-solid fa-user-group"></i><span>Nhân viên</span></a>
                @endif
                <div class="admin-nav-label mt-4">Truy cập nhanh</div>
                <a href="{{ route('shop.index') }}"><i class="fa-solid fa-store"></i><span>Xem cửa hàng</span></a>
            </nav>
            <div class="admin-user"><div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-user fs-4"></i><div><strong class="d-block">{{ Auth::user()->name ?? 'Admin' }}</strong><span>{{ Auth::user()->role === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}</span></div></div><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Đăng xuất</button></form></div>
        </div>
        <div class="admin-main">
            <div class="admin-topbar"><div class="d-flex align-items-center gap-3"><button class="admin-mobile-toggle" type="button" onclick="document.body.classList.toggle('sidebar-open')" aria-label="Mở menu"><i class="fa-solid fa-bars"></i></button><div class="admin-topbar-title">{{ request()->routeIs('air_conditioners.*') ? 'Quản lý sản phẩm' : (request()->routeIs('admin.chat.*') ? 'Tin nhắn khách hàng' : (request()->routeIs('admin.orders.*') ? 'Quản lý đơn hàng' : (request()->routeIs('admin.coupons.*') ? 'Quản lý mã giảm giá' : (request()->routeIs('admin.customers.*') ? 'Quản lý khách hàng' : (request()->routeIs('admin.staff.*') ? 'Quản lý nhân viên' : (request()->routeIs('admin.finance.*') ? 'Tài chính' : 'Báo cáo')))))) }}</div></div><div class="admin-topbar-meta"><i class="fa-regular fa-calendar me-1"></i>{{ now()->format('d/m/Y') }}</div></div>
            <main class="admin-content {{ $isChatPage ? 'admin-chat-content' : '' }}">@yield('content')</main>
        </div>
    @else

    <!-- Header / Navbar -->
    <header class="bg-hc text-white py-2 shadow-sm sticky-top">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Logo -->
            <a href="{{ route('shop.index') }}" class="text-white text-decoration-none fw-bold fs-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-snowflake"></i> HC <span class="fs-6 fw-normal">Electric</span>
            </a>
            
            <!-- Thanh tìm kiếm -->
            <div class="w-50 d-none d-md-block">
                <form action="{{ route('shop.index') }}" method="GET" class="input-group m-0">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control rounded-start-pill border-0 px-3" placeholder="Tìm kiếm điều hòa Casper, Daikin, Panasonic...">
                    <button class="btn btn-warning rounded-end-pill px-4" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>

            <!-- Nút Giỏ hàng & Tài khoản -->
            <div class="d-flex align-items-center gap-3">
                @if(!auth()->check() || Auth::user()->role === 'user')
                    <a href="{{ route('user.cart.index') }}" class="btn btn-outline-light position-relative">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="d-none d-md-inline ms-1">Giỏ hàng</span>
                    </a>
                @endif

                @auth
                    <!-- Đã đăng nhập -->
                    <div class="dropdown">
                        <button class="btn btn-warning dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                            <i class="fa-solid fa-user me-1"></i> {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            @if(Auth::user()->role === 'admin')
                                <li><a class="dropdown-item" href="{{ route('admin.chat.index') }}"><i class="fa-solid fa-comments me-2"></i>Tin nhắn khách hàng</a></li>
                            @elseif(Auth::user()->role === 'staff')
                                <li><a class="dropdown-item" href="{{ route('air_conditioners.index') }}"><i class="fa-solid fa-boxes-stacked me-2"></i>Quản lý sản phẩm</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}"><i class="fa-solid fa-bag-shopping me-2"></i>Quản lý đơn hàng</a></li>
                            @else
                                <li><a class="dropdown-item" href="{{ route('chat.index') }}"><i class="fa-solid fa-comments me-2"></i>Chat với shop</a></li>
                            @endif
                            @if(Auth::user()->role === 'user')
                                <li><a class="dropdown-item" href="{{ route('user.orders.index') }}"><i class="fa-solid fa-clock-rotate-left me-2"></i>Đơn hàng của tôi</a></li>
                            @endif
                            @if(Auth::user()->role === 'admin')
                                <li><a class="dropdown-item text-primary fw-bold" href="{{ route('air_conditioners.index') }}"><i class="fa-solid fa-user-gear me-2"></i>Trang Admin</a></li>
                                <li><a class="dropdown-item text-success fw-bold" href="{{ route('admin.reports.index') }}"><i class="fa-solid fa-chart-line me-2"></i>Báo cáo doanh thu</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <!-- Chưa đăng nhập -->
                    <a href="{{ route('login') }}" class="text-white text-decoration-none fw-bold"><i class="fa-regular fa-user me-1"></i> Đăng nhập</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Nội dung chính của View -->
    <main class="{{ $isChatPage ? 'chat-layout-main' : 'py-4 flex-grow-1' }}">
        @yield('content')
    </main>

    <!-- Footer -->
    @if(!$isChatPage)
    <footer class="bg-dark text-white pt-4 pb-3 mt-auto">
        <div class="container text-center text-md-start">
            <div class="row g-3">
                <div class="col-md-4">
                    <h5 class="text-uppercase fw-bold text-hc">Siêu Thị Điện Máy HC</h5>
                    <p class="small text-secondary">Hệ thống bán lẻ điện máy uy tín hàng đầu, cung cấp các dòng điều hòa chính hãng giá rẻ nhất thị trường.</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Tổng đài hỗ trợ</h6>
                    <p class="mb-1 small text-secondary">Mua hàng: <strong class="text-warning">1800 1788</strong></p>
                    <p class="small text-secondary">Bảo hành: <strong class="text-warning">1900 1788</strong></p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Chính sách ưu đãi</h6>
                    <p class="small text-secondary mb-1">✓ Bao xài 1 đổi 1 trong 30 ngày</p>
                    <p class="small text-secondary">✓ Miễn phí vận chuyển & Lắp đặt tận nơi</p>
                </div>
            </div>
            <hr class="border-secondary my-3">
            <div class="text-center small text-secondary">
                © {{ date('Y') }} HC Electric. All rights reserved.
            </div>
        </div>
    </footer>
    @endif

    @endif

    <!-- Bootstrap 5 JS Bundle (Bao gồm cả Popper.js bắt buộc cho Dropdown) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>