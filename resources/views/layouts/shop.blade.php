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
        body.chat-layout > header { position: relative; z-index: 11000 !important; }
        body.chat-layout > header .dropdown-menu { z-index: 11001 !important; }
        body.chat-layout > .chat-layout-main { position: relative; z-index: 1; }
        .dropdown-menu { z-index: 10000 !important; }
        .shop-toast-container { position: fixed; top: 88px; right: 24px; z-index: 1200; width: min(390px, calc(100vw - 32px)); }
        .shop-toast { border: 0; border-left: 4px solid #198754; border-radius: 12px; box-shadow: 0 14px 35px rgba(15, 23, 42, .18); animation: shop-toast-in .25s ease-out; }
        .shop-toast.toast-error { border-left-color: #d71921; }
        .shop-toast.is-closing { animation: shop-toast-out .25s ease-in forwards; }
        @keyframes shop-toast-in { from { opacity: 0; transform: translateY(-12px) translateX(12px); } to { opacity: 1; transform: translateY(0) translateX(0); } }
        @keyframes shop-toast-out { to { opacity: 0; transform: translateY(-12px) translateX(12px); } }
        .admin-shell { --admin-ink: #172033; --admin-muted: #7b879b; --admin-line: #e7ebf2; background: #f5f7fb; min-height: 100vh; color: var(--admin-ink); }
        .admin-shell .admin-sidebar { width: 256px; background: #172033; color: #fff; position: fixed; inset: 0 auto 0 0; z-index: 1030; display: flex; flex-direction: column; overflow: hidden; }
        .admin-shell .admin-brand { height: 76px; flex: 0 0 76px; padding: 0 22px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,.1); color: #fff; text-decoration: none; font-weight: 800; font-size: 1.1rem; white-space: nowrap; }
        .admin-shell .admin-brand-mark { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 10px; background: #e21b23; color: #fff; }
        .admin-shell .admin-nav { min-height: 0; overflow-y: auto; overflow-x: hidden; padding: 22px 13px 28px; flex: 1 1 auto; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.25) transparent; }
        .admin-shell .admin-nav::-webkit-scrollbar { width: 6px; }
        .admin-shell .admin-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.25); border-radius: 999px; }
        .admin-shell .admin-nav-label { color: #7f8ba0; text-transform: uppercase; letter-spacing: .12em; font-size: .66rem; font-weight: 800; padding: 0 12px 10px; }
        .admin-shell .admin-nav a { color: #b9c2d1; border-radius: 10px; display: flex; align-items: center; gap: 12px; min-height: 42px; padding: 10px 12px; margin: 3px 0; text-decoration: none; font-size: .9rem; transition: .18s ease; white-space: nowrap; }
        .admin-shell .admin-nav a i { flex: 0 0 20px; width: 20px; text-align: center; color: #8290a8; }
        .admin-shell .admin-nav a > span:not(.nav-badge):not(.admin-notification-badge) { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
        .admin-shell .admin-nav a:hover, .admin-shell .admin-nav a.active { background: rgba(255,255,255,.1); color: #fff; }
        .admin-shell .admin-nav a.active { box-shadow: inset 3px 0 #e21b23; }
        .admin-shell .admin-nav a.active i { color: #ff6c72; }
        .admin-shell .admin-nav .nav-badge { margin-left: auto; min-width: 22px; padding: 3px 6px; border-radius: 999px; background: #e21b23; color: #fff; font-size: .68rem; line-height: 1; text-align: center; }
        .admin-shell .admin-notification-badge { margin-left: auto; min-width: 20px; padding: 3px 5px; border-radius: 999px; background: #f59e0b; color: #172033; font-size: .68rem; line-height: 1; text-align: center; }
        .admin-shell .admin-user { flex: 0 0 auto; border-top: 1px solid rgba(255,255,255,.1); padding: 16px 18px; color: #c5ccda; font-size: .82rem; background: #172033; }
        .admin-shell .admin-user form { margin-top: 10px; }
        .admin-shell .admin-user button { color: #ff9da1; background: transparent; border: 0; padding: 0; font-size: .8rem; }
        .admin-shell .admin-main { margin-left: 256px; width: calc(100% - 256px); min-width: 0; min-height: 100vh; overflow-x: hidden; }
        .admin-shell .admin-topbar { height: 76px; background: #fff; border-bottom: 1px solid var(--admin-line); display: flex; align-items: center; justify-content: space-between; padding: 0 34px; }
        .admin-shell .admin-topbar-title { font-weight: 800; font-size: 1.05rem; }
        .admin-shell .admin-topbar-meta { color: var(--admin-muted); font-size: .8rem; }
        .admin-shell .admin-content { padding: 30px 34px; }
        .admin-shell .admin-mobile-toggle { display: none; }
        .admin-shell .admin-content, .admin-shell .admin-content > * { min-width: 0; max-width: 100%; }
        @media (max-width: 991.98px) { .admin-shell .admin-sidebar { transform: translateX(-100%); transition: transform .2s ease; } .admin-shell.sidebar-open .admin-sidebar { transform: translateX(0); } .admin-shell .admin-main { margin-left: 0; width: 100%; } .admin-shell .admin-mobile-toggle { display: inline-grid; place-items: center; border: 0; background: transparent; font-size: 1.2rem; color: var(--admin-ink); } .admin-shell .admin-topbar { padding: 0 20px; } .admin-shell .admin-content { padding: 22px 18px; } }
    </style>
</head>
@php
    $isAdminArea = request()->is('admin/*') || request()->routeIs('air_conditioners.*');
    $isChatPage = request()->routeIs('chat.*', 'admin.chat.*');
@endphp
<body class="{{ $isAdminArea ? 'admin-shell' : 'bg-light d-flex flex-column min-vh-100' }} {{ $isChatPage ? 'chat-layout' : '' }}">
    <div id="shop-toast-container" class="shop-toast-container" aria-live="polite" aria-atomic="true">
        @if(session('success'))
            <div class="shop-toast alert alert-success d-flex align-items-center gap-2 mb-2" role="alert">
                <i class="fa-solid fa-circle-check"></i><span>{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-dismiss-shop-toast aria-label="Đóng"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="shop-toast alert alert-danger toast-error d-flex align-items-center gap-2 mb-2" role="alert">
                <i class="fa-solid fa-triangle-exclamation"></i><span>{{ session('error') }}</span>
                <button type="button" class="btn-close ms-auto" data-dismiss-shop-toast aria-label="Đóng"></button>
            </div>
        @endif
    </div>

    @if($isAdminArea)
        @php
            $adminPendingOrderCount = \App\Models\Order::where('status', \App\Models\Order::STATUS_PENDING_CONFIRMATION)->count();
            $adminLowStockCount = \Illuminate\Support\Facades\Schema::hasTable('air_conditioner_variants')
                ? \App\Models\AirConditionerVariant::where('stock', '<=', 5)->count()
                : 0;
            $adminUnreadMessageCount = \Illuminate\Support\Facades\Schema::hasTable('chat_messages')
                && \Illuminate\Support\Facades\Schema::hasColumn('chat_messages', 'read_at')
                ? \App\Models\ChatMessage::where('sender_role', 'customer')->whereNull('read_at')->count() : 0;
            if (\Illuminate\Support\Facades\Schema::hasTable('order_messages')
                && \Illuminate\Support\Facades\Schema::hasColumn('order_messages', 'read_at')) {
                $adminUnreadMessageCount += \App\Models\OrderMessage::where('sender_role', 'customer')->whereNull('read_at')->count();
            }
        @endphp
        <div class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span class="admin-brand-mark"><i class="fa-solid fa-snowflake"></i></span><span>HC <small class="fw-normal">{{ Auth::user()->role === 'admin' ? 'ADMIN' : 'STAFF' }}</small></span></a>
            <nav class="admin-nav">
                <div class="admin-nav-label">Workspace</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fa-solid fa-chart-line"></i><span>Tổng quan</span></a>
                <div class="admin-nav-label mt-4">Bán hàng</div>
                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><i class="fa-solid fa-bag-shopping"></i><span>Đơn hàng</span>@if($adminPendingOrderCount > 0)<span class="nav-badge">{{ $adminPendingOrderCount > 99 ? '99+' : $adminPendingOrderCount }}</span>@endif</a>
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"><i class="fa-solid fa-users"></i><span>Khách hàng</span></a>
                @endif
                <a id="admin-chat-link" href="{{ route('admin.chat.index') }}" class="{{ request()->routeIs('admin.chat.*') ? 'active' : '' }}"><i class="fa-solid fa-comments"></i><span>Tin nhắn</span>@if($adminUnreadMessageCount > 0)<span class="nav-badge" id="admin-message-badge">{{ $adminUnreadMessageCount > 99 ? '99+' : $adminUnreadMessageCount }}</span>@endif</a>
                <a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"><i class="fa-solid fa-ticket"></i><span>Mã giảm giá</span></a>
                <div class="admin-nav-label mt-4">Kho & sản phẩm</div>
                <a href="{{ route('air_conditioners.index') }}" class="{{ request()->routeIs('air_conditioners.*') ? 'active' : '' }}"><i class="fa-solid fa-boxes-stacked"></i><span>Sản phẩm</span>@if($adminLowStockCount > 0)<span class="nav-badge">{{ $adminLowStockCount > 99 ? '99+' : $adminLowStockCount }}</span>@endif</a>
                @if(Auth::user()->role === 'admin')
                    <div class="admin-nav-label mt-4">Tài chính & báo cáo</div>
                    <a href="{{ route('admin.finance.index') }}" class="{{ request()->routeIs('admin.finance.*') ? 'active' : '' }}"><i class="fa-solid fa-wallet"></i><span>Tài chính</span></a>
                    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><i class="fa-solid fa-chart-line"></i><span>Báo cáo</span></a>
                    <div class="admin-nav-label mt-4">Quản trị hệ thống</div>
                    <a href="{{ route('admin.activity-logs.index') }}" class="{{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}"><i class="fa-solid fa-clock-rotate-left"></i><span>Nhật ký hoạt động</span></a>
                    <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><i class="fa-solid fa-user-group"></i><span>Nhân viên</span></a>
                @endif
                <div class="admin-nav-label mt-4">Truy cập nhanh</div>
                <a href="{{ route('shop.index') }}"><i class="fa-solid fa-store"></i><span>Xem cửa hàng</span></a>
            </nav>
            <div class="admin-user"><div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-user fs-4"></i><div><strong class="d-block">{{ Auth::user()->name ?? 'Admin' }}</strong><span>{{ Auth::user()->role === 'admin' ? 'Quản trị viên' : 'Nhân viên' }}</span></div></div><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Đăng xuất</button></form></div>
        </div>
        <div class="admin-main">
            <div class="admin-topbar"><div class="d-flex align-items-center gap-3"><button class="admin-mobile-toggle" type="button" onclick="document.body.classList.toggle('sidebar-open')" aria-label="Mở menu"><i class="fa-solid fa-bars"></i></button><div class="admin-topbar-title">{{ request()->routeIs('admin.dashboard') ? 'Tổng quan' : (request()->routeIs('air_conditioners.*') ? 'Quản lý sản phẩm' : (request()->routeIs('admin.chat.*') ? 'Tin nhắn khách hàng' : (request()->routeIs('admin.orders.*') ? 'Quản lý đơn hàng' : (request()->routeIs('admin.coupons.*') ? 'Quản lý mã giảm giá' : (request()->routeIs('admin.customers.*') ? 'Quản lý khách hàng' : (request()->routeIs('admin.staff.*') ? 'Quản lý nhân viên' : (request()->routeIs('admin.finance.*') ? 'Tài chính' : 'Báo cáo'))))))) }}</div></div><div class="admin-topbar-meta"><i class="fa-regular fa-calendar me-1"></i>{{ now()->format('d/m/Y') }}</div></div>
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
                        <span data-cart-count class="badge text-bg-warning text-dark position-absolute top-0 start-100 translate-middle {{ collect(session('cart', []))->sum('quantity') ? '' : 'd-none' }}">{{ collect(session('cart', []))->sum('quantity') }}</span>
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
                                <li><a class="dropdown-item" href="{{ route('account.index') }}"><i class="fa-solid fa-user-gear me-2"></i>Tài khoản của tôi</a></li>
                                <li><a class="dropdown-item" href="{{ route('user.orders.index') }}"><i class="fa-solid fa-clock-rotate-left me-2"></i>Đơn hàng của tôi</a></li>
                                <li><a class="dropdown-item" href="{{ route('wishlist.index') }}"><i class="fa-regular fa-heart me-2"></i>Danh sách yêu thích</a></li>
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
                    <p class="small text-secondary">✓ Vận chuyển nhanh chóng, phí giao hàng hiển thị rõ ràng khi đặt hàng</p>
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
    <script>
        (() => {
            const toastContainer = document.getElementById('shop-toast-container');

            const removeToast = (toast) => {
                if (!toast) return;
                toast.classList.add('is-closing');
                window.setTimeout(() => toast.remove(), 250);
            };

            const showToast = (message, type = 'success') => {
                if (!toastContainer || !message) return;
                const toast = document.createElement('div');
                toast.className = `shop-toast alert alert-${type === 'error' ? 'danger toast-error' : 'success'} d-flex align-items-center gap-2 mb-2`;
                toast.setAttribute('role', 'alert');
                toast.innerHTML = `<i class="fa-solid fa-${type === 'error' ? 'triangle-exclamation' : 'circle-check'}"></i><span></span><button type="button" class="btn-close ms-auto" data-dismiss-shop-toast aria-label="Đóng"></button>`;
                toast.querySelector('span').textContent = message;
                toast.querySelector('[data-dismiss-shop-toast]').addEventListener('click', () => removeToast(toast));
                toastContainer.appendChild(toast);
                window.setTimeout(() => removeToast(toast), 5000);
            };
            window.shopShowToast = showToast;

            const updateCompareCount = (count) => {
                document.querySelectorAll('[data-compare-count]').forEach((badge) => {
                    badge.textContent = count;
                    badge.classList.toggle('d-none', count < 1);
                });
            };

            const updateCartCount = (count) => {
                document.querySelectorAll('[data-cart-count]').forEach((badge) => {
                    badge.textContent = count;
                    badge.classList.toggle('d-none', count < 1);
                });
            };

            document.querySelectorAll('[data-dismiss-shop-toast]').forEach((button) => {
                button.addEventListener('click', () => removeToast(button.closest('.shop-toast')));
            });
            document.querySelectorAll('.shop-toast').forEach((toast) => {
                window.setTimeout(() => removeToast(toast), 5000);
            });

            @if($isAdminArea && auth()->check() && in_array(Auth::user()->role, ['admin', 'staff'], true))
            const refreshAdminNotifications = async () => {
                try {
                    const response = await fetch('{{ route('admin.notifications') }}', { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();
                    const total = document.getElementById('admin-notification-total');
                    if (total) total.textContent = data.total > 99 ? '99+' : data.total;
                    let messageBadge = document.getElementById('admin-message-badge');
                    const chatLink = document.getElementById('admin-chat-link');
                    if (data.counts.messages > 0 && !messageBadge && chatLink) {
                        messageBadge = document.createElement('span');
                        messageBadge.id = 'admin-message-badge';
                        messageBadge.className = 'nav-badge';
                        chatLink.appendChild(messageBadge);
                    }
                    if (messageBadge) {
                        messageBadge.textContent = data.counts.messages > 99 ? '99+' : data.counts.messages;
                        messageBadge.classList.toggle('d-none', data.counts.messages < 1);
                    }
                } catch (error) { /* polling is intentionally best effort */ }
            };
            refreshAdminNotifications();
            window.setInterval(refreshAdminNotifications, 30000);
            @endif

            document.addEventListener('submit', async (event) => {
                const form = event.target.closest('form[data-ajax-toast]');
                if (!form) return;
                if (event.submitter && event.submitter.name === 'buy_now') return;

                event.preventDefault();
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) submitButton.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        body: new FormData(form),
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Không thể thực hiện thao tác.');
                    if (typeof data.compare_count === 'number') {
                        updateCompareCount(data.compare_count);
                    }
                    if (typeof data.cart_count === 'number') {
                        updateCartCount(data.cart_count);
                    }
                    showToast(data.message);
                    if (form.hasAttribute('data-compare-remove')) {
                        window.setTimeout(() => window.location.reload(), 250);
                    }
                } catch (error) {
                    showToast(error.message || 'Đã xảy ra lỗi. Vui lòng thử lại.', 'error');
                } finally {
                    if (submitButton) submitButton.disabled = false;
                }
            });
        })();
    </script>
</body>
</html>