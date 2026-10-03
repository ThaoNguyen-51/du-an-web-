@extends('layouts.shop')

@section('content')
@php
    $statusLabels = [
        'pending_confirmation' => 'Chờ xác nhận',
        'awaiting_pickup' => 'Chờ lấy hàng',
        'awaiting_delivery' => 'Chờ giao hàng',
        'in_transit' => 'Đang giao',
        'completed' => 'Đã hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
    $statusStyles = [
        'pending_confirmation' => ['class' => 'status-waiting', 'icon' => 'fa-hourglass-half'],
        'awaiting_pickup' => ['class' => 'status-pickup', 'icon' => 'fa-box'],
        'awaiting_delivery' => ['class' => 'status-delivery', 'icon' => 'fa-truck'],
        'in_transit' => ['class' => 'status-transit', 'icon' => 'fa-route'],
        'completed' => ['class' => 'status-complete', 'icon' => 'fa-circle-check'],
        'cancelled' => ['class' => 'status-cancelled', 'icon' => 'fa-ban'],
    ];
    $statusCounts = $orders->groupBy(fn ($order) => \App\Models\Order::normalizeStatus($order->status))->map->count();
    $totalValue = $orders->sum('total_amount');
    $pendingCount = $statusCounts->get('pending_confirmation', 0);
@endphp

<style>
    .admin-orders-page { --ink: #172033; --muted: #748096; --line: #e7ebf2; --accent: #e21b23; background: #f5f7fb; min-height: calc(100vh - 72px); }
    .admin-orders-page .container { max-width: 1440px; }
    .orders-hero { background: linear-gradient(120deg, #172033 0%, #26334d 65%, #e21b23 160%); color: #fff; border-radius: 20px; padding: 28px 32px; position: relative; overflow: hidden; }
    .orders-hero::after { content: ''; position: absolute; width: 240px; height: 240px; right: -70px; top: -100px; border: 34px solid rgba(255,255,255,.08); border-radius: 50%; }
    .hero-kicker { color: #ffb9bd; font-size: .76rem; letter-spacing: .14em; text-transform: uppercase; font-weight: 700; }
    .hero-copy { color: #cbd3e2; margin: 0; }
    .hero-actions { position: relative; z-index: 1; }
    .hero-actions .btn { border-color: rgba(255,255,255,.3); color: #fff; }
    .hero-actions .btn:hover { background: #fff; color: var(--ink); }
    .metric-card { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 18px; height: 100%; box-shadow: 0 8px 24px rgba(28, 43, 72, .05); }
    .metric-icon { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 12px; font-size: 1.05rem; }
    .metric-label { color: var(--muted); font-size: .82rem; margin-top: 14px; }
    .metric-value { color: var(--ink); font-size: 1.35rem; font-weight: 800; margin-top: 3px; }
    .filter-panel, .orders-panel { background: #fff; border: 1px solid var(--line); border-radius: 16px; box-shadow: 0 8px 24px rgba(28, 43, 72, .04); }
    .filter-panel { padding: 18px; }
    .filter-panel .form-control, .filter-panel .form-select { border-color: #dce2ec; min-height: 44px; }
    .filter-panel .form-control:focus, .filter-panel .form-select:focus { border-color: #e21b23; box-shadow: 0 0 0 .2rem rgba(226,27,35,.1); }
    .orders-panel { overflow: hidden; }
    .orders-panel-head { padding: 20px 22px; border-bottom: 1px solid var(--line); }
    .orders-table { margin: 0; min-width: 940px; }
    .orders-table thead th { color: #8a95a8; background: #fbfcfe; border-bottom: 1px solid var(--line); font-size: .7rem; letter-spacing: .08em; text-transform: uppercase; padding: 14px 16px; white-space: nowrap; }
    .orders-table tbody td { border-color: var(--line); padding: 17px 16px; vertical-align: middle; color: var(--ink); }
    .orders-table tbody tr:hover { background: #fcfdff; }
    .order-id { color: var(--ink); font-weight: 800; }
    .order-date, .customer-phone, .order-meta { color: var(--muted); font-size: .78rem; }
    .customer-name { font-weight: 700; }
    .amount { color: var(--accent); font-weight: 800; white-space: nowrap; }
    .status-pill, .payment-pill { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 6px 10px; font-size: .73rem; font-weight: 700; white-space: nowrap; }
    .status-waiting { color: #9a6500; background: #fff4d6; }
    .status-pickup { color: #2864a8; background: #e8f2ff; }
    .status-delivery { color: #5b45a3; background: #f0ebff; }
    .status-transit { color: #087b73; background: #e2f7f4; }
    .status-complete { color: #177b4c; background: #e2f6eb; }
    .status-cancelled { color: #a23945; background: #fde9eb; }
    .payment-paid { color: #177b4c; background: #e2f6eb; }
    .payment-pending { color: #9a6500; background: #fff4d6; }
    .payment-failed { color: #a23945; background: #fde9eb; }
    .order-actions { display: flex; gap: 7px; justify-content: flex-end; }
    .order-actions .btn { border-radius: 9px; }
    .detail-drawer { background: #f8faff; border-top: 1px solid var(--line); }
    .detail-drawer .detail-inner { padding: 20px 22px; }
    .detail-box { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 15px; height: 100%; }
    .detail-title { font-size: .78rem; color: var(--muted); font-weight: 800; letter-spacing: .07em; text-transform: uppercase; margin-bottom: 12px; }
    .message-scroll { max-height: 190px; overflow-y: auto; }
    .message-bubble { border-radius: 12px; padding: 9px 11px; font-size: .84rem; margin-bottom: 8px; }
    .message-admin { background: #e21b23; color: #fff; margin-left: 18px; }
    .message-customer { background: #f0f3f8; color: var(--ink); margin-right: 18px; }
    .empty-state { padding: 70px 20px; text-align: center; color: var(--muted); }
    @media (max-width: 767.98px) { .orders-hero { padding: 22px; border-radius: 14px; } .hero-actions { margin-top: 18px; } .orders-table { min-width: 850px; } }
</style>

<div class="admin-orders-page py-4 py-lg-5">
    <div class="container">
        <div class="orders-hero mb-4">
            <div class="row align-items-center g-3 position-relative">
                <div class="col-lg-8"><div class="hero-kicker">Operations center</div><h1 class="hero-title">Quản lý đơn hàng</h1><p class="hero-copy">Theo dõi, xử lý và chăm sóc từng đơn hàng trong một nơi.</p></div>
                <div class="col-lg-4 text-lg-end hero-actions"><a href="{{ route('air_conditioners.index') }}" class="btn btn-outline-light"><i class="fa-solid fa-arrow-left me-2"></i>Quản lý sản phẩm</a></div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success border-0 shadow-sm mb-4"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $errors->first() }}</div>@endif

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3"><div class="metric-card"><div class="metric-icon" style="background:#e9efff;color:#4267c7"><i class="fa-solid fa-layer-group"></i></div><div class="metric-label">Tổng đơn trong bộ lọc</div><div class="metric-value">{{ $orders->count() }}</div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="metric-card"><div class="metric-icon" style="background:#fff1d9;color:#b87908"><i class="fa-solid fa-hourglass-half"></i></div><div class="metric-label">Chờ xác nhận</div><div class="metric-value">{{ $pendingCount }}</div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="metric-card"><div class="metric-icon" style="background:#e2f6eb;color:#177b4c"><i class="fa-solid fa-circle-check"></i></div><div class="metric-label">Đã hoàn thành</div><div class="metric-value">{{ $statusCounts->get('completed', 0) }}</div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="metric-card"><div class="metric-icon" style="background:#fde9eb;color:#c33743"><i class="fa-solid fa-coins"></i></div><div class="metric-label">Tổng giá trị đơn</div><div class="metric-value">{{ number_format((float) $totalValue, 0, ',', '.') }} đ</div></div></div>
        </div>

        <div class="filter-panel mb-4"><div class="d-flex justify-content-between align-items-center mb-3"><div><strong class="fs-5 d-block">Danh sách đơn hàng</strong><div class="small text-muted">Tìm khách hàng theo tên, mã đơn hàng, mã vận đơn và nội dung tin nhắn.</div></div><span class="badge rounded-pill text-bg-light border">{{ $orders->count() }} kết quả</span></div>
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2"><div class="col-lg-6"><div class="input-group"><span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span><input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control border-start-0" placeholder="Tên khách, mã vận đơn, tin nhắn, mã đơn..."></div></div><div class="col-lg-3"><select name="status" class="form-select"><option value="">Tất cả trạng thái</option>@foreach($statusLabels as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></div><div class="col-lg-3 d-flex gap-2"><button type="submit" class="btn btn-danger flex-grow-1"><i class="fa-solid fa-filter me-1"></i>Lọc đơn</button><a href="{{ route('admin.orders.index') }}" class="btn btn-light border" title="Xóa bộ lọc"><i class="fa-solid fa-rotate-left"></i></a></div></form>
        </div>

        <div class="orders-panel"><div class="orders-panel-head d-flex justify-content-between align-items-center"><div><strong>Đơn hàng gần đây</strong><div class="small text-muted mt-1">Mở chi tiết để xem sản phẩm và trao đổi với khách.</div></div><i class="fa-solid fa-arrow-down-wide-short text-muted"></i></div>
            @if($orders->isEmpty())<div class="empty-state"><i class="fa-solid fa-box-open fa-3x mb-3"></i><h5>Chưa có đơn hàng phù hợp</h5><p class="mb-0">Thử thay đổi bộ lọc để xem thêm kết quả.</p></div>
            @else
                <div class="table-responsive"><table class="table orders-table align-middle"><thead><tr><th>Đơn hàng</th><th>Khách hàng</th><th>Thanh toán</th><th>Trạng thái đơn</th><th class="text-end">Giá trị</th><th class="text-end">Thao tác</th></tr></thead><tbody>
                @foreach($orders as $order)
                    @php
                        $normalizedStatus = \App\Models\Order::normalizeStatus($order->status);
                        $statusStyle = $statusStyles[$normalizedStatus] ?? ['class' => 'status-cancelled', 'icon' => 'fa-circle-question'];
                        $paymentTransaction = $order->paymentTransactions->sortByDesc('id')->sortByDesc(fn ($transaction) => in_array($transaction->status, ['paid', 'refund_pending', 'refunded'], true))->first();
                        $paymentGateway = match ($order->payment_method) {
                            'cod' => 'COD',
                            'visa' => 'VISA',
                            'domestic' => 'THẺ NỘI ĐỊA',
                            default => strtoupper($order->payment_method ?? 'UNKNOWN'),
                        };
                        $paymentStatus = $paymentTransaction?->status ?? 'pending';
                        $detailId = 'order-detail-' . $order->id;
                    @endphp
                    <tr><td><div class="order-id">#{{ $order->id }}</div><div class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</div></td><td><div class="customer-name">{{ $order->customer_name }}</div><div class="customer-phone">{{ $order->customer_phone }}</div></td><td><span class="payment-pill {{ $paymentStatus === 'paid' ? 'payment-paid' : ($paymentStatus === 'failed' ? 'payment-failed' : 'payment-pending') }}"><i class="fa-solid {{ $paymentStatus === 'paid' ? 'fa-check' : 'fa-clock' }}"></i>{{ $paymentStatus === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}</span><div class="order-meta mt-1">{{ strtoupper($paymentGateway) }}</div></td><td><span class="status-pill {{ $statusStyle['class'] }}"><i class="fa-solid {{ $statusStyle['icon'] }}"></i>{{ $statusLabels[$normalizedStatus] ?? $order->status }}</span></td><td class="text-end"><div class="amount">{{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</div><div class="order-meta">{{ $order->items->count() }} sản phẩm</div></td><td><div class="order-actions"><button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $detailId }}" aria-expanded="false" title="Mở phản hồi"><i class="fa-solid fa-comments me-1"></i>Phản hồi</button><form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="d-flex gap-1">@csrf<select name="status" class="form-select form-select-sm" title="Trạng thái mới"><option value="pending_confirmation" @selected($normalizedStatus === 'pending_confirmation')>Chờ xác nhận</option><option value="awaiting_pickup" @selected($normalizedStatus === 'awaiting_pickup')>Chờ lấy hàng</option><option value="awaiting_delivery" @selected($normalizedStatus === 'awaiting_delivery')>Chờ giao hàng</option><option value="in_transit" @selected($normalizedStatus === 'in_transit')>Đang giao</option><option value="completed" @selected($normalizedStatus === 'completed')>Hoàn thành</option>@if(!in_array($normalizedStatus, ['awaiting_delivery', 'in_transit', 'completed']))<option value="cancelled" @selected($normalizedStatus === 'cancelled')>Đã hủy</option>@endif</select><button class="btn btn-sm btn-danger" title="Lưu trạng thái"><i class="fa-solid fa-check"></i></button></form></div></td></tr>
                    <tr class="collapse" id="{{ $detailId }}"><td colspan="6" class="p-0"><div class="detail-drawer"><div class="detail-inner"><div class="row g-3"><div class="col-lg-4"><div class="detail-box"><div class="detail-title">Thông tin nhận hàng</div><div class="small mb-2"><i class="fa-solid fa-user me-2 text-danger"></i>{{ $order->customer_name }}</div><div class="small mb-2"><i class="fa-solid fa-phone me-2 text-danger"></i>{{ $order->customer_phone }}</div><div class="small"><i class="fa-solid fa-location-dot me-2 text-danger"></i>{{ $order->customer_address }}</div></div></div><div class="col-lg-4"><div class="detail-box"><div class="detail-title">Sản phẩm</div>@foreach($order->items as $item)<div class="d-flex justify-content-between gap-2 small border-bottom py-2"><span>{{ $item->product_name ?? 'Sản phẩm' }} <span class="text-muted">x{{ $item->quantity }}</span></span><strong>{{ number_format((float) ($item->subtotal ?? $item->price * $item->quantity), 0, ',', '.') }} đ</strong></div>@endforeach</div></div><div class="col-lg-4"><div class="detail-box"><div class="detail-title">Trao đổi với khách</div><div class="message-scroll mb-2">@forelse($order->messages as $message)<div class="message-bubble {{ $message->sender_role === 'admin' ? 'message-admin' : 'message-customer' }}">{{ $message->message }}<div class="small opacity-75 mt-1">{{ $message->created_at->format('d/m H:i') }}</div></div>@empty<div class="small text-muted py-2">Chưa có tin nhắn.</div>@endforelse</div><form action="{{ route('admin.orders.sendMessage', $order->id) }}" method="POST" class="input-group input-group-sm">@csrf<input type="text" name="message" class="form-control" placeholder="Nhắn tin..." maxlength="1000" required><button class="btn btn-danger"><i class="fa-solid fa-paper-plane"></i></button></form></div></div></div></div></div></td></tr>
                @endforeach
                </tbody></table></div>
            @endif
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('.orders-table select[name="status"]').forEach((select) => {
        if (select.value !== 'cancelled') return;
        select.disabled = true;
        const saveButton = select.closest('form')?.querySelector('button');
        if (saveButton) saveButton.disabled = true;
    });
</script>
@endsection


