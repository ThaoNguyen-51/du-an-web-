@extends('layouts.shop')

@section('content')
<style>
    .dashboard-page { --dashboard-ink: #172033; --dashboard-muted: #7b879b; }
    .dashboard-page, .dashboard-page .row, .dashboard-page .card { min-width: 0; max-width: 100%; }
    .dashboard-page .card-header > div { min-width: 0; }
    .dashboard-page .dashboard-section { overflow: hidden; }
    .dashboard-page .min-width-0 { min-width: 0; overflow: hidden; }
    .dashboard-page .dashboard-section .badge { flex: 0 1 auto; max-width: 46%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dashboard-page .dashboard-alert { min-width: 0; }
    .dashboard-page .dashboard-alert > .flex-grow-1 { min-width: 0; }
    .dashboard-page .dashboard-alert .btn { flex: 0 0 auto; white-space: nowrap; }
    .dashboard-hero { background: linear-gradient(135deg, #172033, #273a5b); color: #fff; border-radius: 18px; }
    .dashboard-hero .hero-icon { width: 54px; height: 54px; display: grid; place-items: center; border-radius: 16px; background: rgba(255,255,255,.12); font-size: 1.35rem; }
    .dashboard-stat { border: 0; border-radius: 16px; box-shadow: 0 8px 22px rgba(15,23,42,.06); }
    .dashboard-stat .stat-icon { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 12px; }
    .dashboard-section { border: 0; border-radius: 16px; box-shadow: 0 8px 22px rgba(15,23,42,.06); }
    .dashboard-section .card-header { border-bottom: 1px solid #edf0f5; }
    .status-pill { border-radius: 999px; padding: .35rem .65rem; font-size: .72rem; font-weight: 700; }
    .status-pending_confirmation { background: #fff4d6; color: #9a6700; }
    .status-completed { background: #dcfce7; color: #166534; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-default { background: #e8eef8; color: #38527a; }
    .dashboard-alert { border-radius: 14px; border: 1px solid transparent; }
    .dashboard-alert.alert-orders { background: #fff8e6; border-color: #ffe3a3; }
    .dashboard-alert.alert-stock { background: #fff1f2; border-color: #ffd0d3; }
    .dashboard-alert .alert-icon { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 11px; }
</style>

<div class="dashboard-page">
    <div class="dashboard-hero p-4 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon"><i class="fa-solid fa-chart-line"></i></div>
            <div>
                <div class="small text-white-50 text-uppercase fw-bold">Tổng quan cửa hàng</div>
                <h1 class="h3 fw-bold mb-1">Chào mừng trở lại, {{ Auth::user()->name }}</h1>
                <p class="mb-0 text-white-50">Theo dõi hoạt động kinh doanh và xử lý công việc nhanh hơn.</p>
            </div>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-light fw-semibold"><i class="fa-solid fa-list-check me-1"></i> Xem đơn hàng</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Doanh thu hôm nay', $todayRevenue, 'fa-calendar-day', 'text-success', 'bg-success-subtle', true],
            ['Tổng doanh thu', $totalRevenue, 'fa-money-bill-trend-up', 'text-primary', 'bg-primary-subtle', true],
            ['Đơn chờ xác nhận', $pendingOrders, 'fa-hourglass-half', 'text-warning', 'bg-warning-subtle', false],
            ['Sản phẩm', $totalProducts, 'fa-boxes-stacked', 'text-danger', 'bg-danger-subtle', false],
        ] as [$label, $value, $icon, $color, $iconBg, $isMoney])
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-stat h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted mb-2">{{ $label }}</div>
                            <div class="fs-4 fw-bold {{ $color }}">{{ $isMoney ? number_format($value, 0, ',', '.') . ' đ' : number_format($value) }}</div>
                        </div>
                        <div class="stat-icon {{ $iconBg }} {{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($pendingOrders > 0 || $lowStockCount > 0 || $outOfStockCount > 0)
        <div class="row g-3 mb-4">
            @if($pendingOrders > 0)
                <div class="col-lg-6">
                    <div class="dashboard-alert alert-orders p-3 d-flex align-items-center gap-3">
                        <div class="alert-icon bg-warning-subtle text-warning"><i class="fa-solid fa-bell"></i></div>
                        <div class="flex-grow-1"><strong class="d-block">Có {{ $pendingOrders }} đơn hàng mới cần xử lý</strong><span class="small text-muted">Các đơn đang chờ xác nhận của cửa hàng.</span></div>
                        <a href="{{ route('admin.orders.index', ['status' => 'pending_confirmation']) }}" class="btn btn-sm btn-warning fw-semibold">Xử lý ngay</a>
                    </div>
                </div>
            @endif
            @if($lowStockCount > 0 || $outOfStockCount > 0)
                <div class="col-lg-6">
                    <div class="dashboard-alert alert-stock p-3 d-flex align-items-center gap-3">
                        <div class="alert-icon bg-danger-subtle text-danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="flex-grow-1"><strong class="d-block">Cảnh báo tồn kho</strong><span class="small text-muted">{{ $outOfStockCount }} biến thể hết hàng, {{ $lowStockCount }} biến thể sắp hết.</span></div>
                        <a href="{{ route('air_conditioners.index', ['stock' => 'low']) }}" class="btn btn-sm btn-outline-danger fw-semibold">Kiểm tra kho</a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card dashboard-section h-100">
                <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                    <div><h2 class="h6 fw-bold mb-1">Đơn hàng gần đây</h2><span class="small text-muted">Các đơn mới nhất trong hệ thống</span></div>
                    <a href="{{ route('admin.orders.index') }}" class="small text-danger fw-semibold text-decoration-none">Xem tất cả <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th class="text-end">Tổng tiền</th><th>Trạng thái</th></tr></thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                @php($status = \App\Models\Order::normalizeStatus($order->status))
                                <tr>
                                    <td class="fw-bold">#{{ $order->id }} @if($status === \App\Models\Order::STATUS_PENDING_CONFIRMATION)<span class="badge rounded-pill text-bg-warning ms-1">Mới</span>@endif</td>
                                    <td>{{ $order->customer_name ?: ($order->user->name ?? 'Khách lẻ') }}</td>
                                    <td class="small text-muted">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                    <td><span class="status-pill status-{{ $status === \App\Models\Order::STATUS_PENDING_CONFIRMATION || $status === \App\Models\Order::STATUS_COMPLETED || $status === \App\Models\Order::STATUS_CANCELLED ? $status : 'default' }}">{{ $order->status_label }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Chưa có đơn hàng.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card dashboard-section h-100">
                <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                    <div><h2 class="h6 fw-bold mb-1">Cảnh báo tồn kho</h2><span class="small text-muted">Sản phẩm còn từ 5 sản phẩm trở xuống</span></div>
                    <a href="{{ route('air_conditioners.index') }}" class="text-danger"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                </div>
                <div class="card-body p-0">
                    @forelse($lowStockVariants as $variant)
                        <div class="d-flex align-items-center justify-content-between gap-2 p-3 border-bottom">
                            <div class="min-width-0"><div class="fw-semibold text-truncate">{{ $variant->airConditioner?->name ?? 'Sản phẩm' }}</div><div class="small text-muted">{{ $variant->capacity_name }}</div></div>
                            <span class="badge rounded-pill {{ $variant->stock <= 0 ? 'text-bg-danger' : 'text-bg-warning' }}">{{ $variant->stock }} còn lại</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5"><i class="fa-solid fa-circle-check text-success fs-3 mb-2"></i><div>Kho hàng đang ổn định.</div></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6"><a href="{{ route('air_conditioners.create') }}" class="card dashboard-section text-decoration-none text-dark"><div class="card-body d-flex align-items-center gap-3"><i class="fa-solid fa-plus text-danger fs-4"></i><div><div class="fw-bold">Thêm sản phẩm</div><div class="small text-muted">Tạo sản phẩm và biến thể mới</div></div></div></a></div>
        @if(Auth::user()->role === 'admin')
            <div class="col-md-6"><a href="{{ route('admin.reports.index') }}" class="card dashboard-section text-decoration-none text-dark"><div class="card-body d-flex align-items-center gap-3"><i class="fa-solid fa-chart-pie text-primary fs-4"></i><div><div class="fw-bold">Xem báo cáo chi tiết</div><div class="small text-muted">Phân tích doanh thu và phương thức thanh toán</div></div></div></a></div>
        @else
            <div class="col-md-6"><a href="{{ route('admin.chat.index') }}" class="card dashboard-section text-decoration-none text-dark"><div class="card-body d-flex align-items-center gap-3"><i class="fa-solid fa-comments text-primary fs-4"></i><div><div class="fw-bold">Xem tin nhắn khách hàng</div><div class="small text-muted">Phản hồi các cuộc trò chuyện mới</div></div></div></a></div>
        @endif
    </div>
</div>
@endsection
