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
    $status = \App\Models\Order::normalizeStatus($order->status);
    $payment = $order->paymentTransactions->sortByDesc('id')->sortByDesc(fn ($item) => in_array($item->status, ['paid', 'refund_pending', 'refunded'], true))->first();
    $paymentLabel = match ($payment?->status) {
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán thất bại',
        default => 'Chưa thanh toán',
    };
    $subtotal = $order->items->sum(fn ($item) => (float) ($item->subtotal ?? ($item->price * $item->quantity)));
@endphp

<style>
    .order-detail-page { background: #f5f7fb; min-height: calc(100vh - 76px); }
    .order-detail-hero, .detail-card { background: #fff; border: 1px solid #e7ebf2; border-radius: 16px; box-shadow: 0 8px 24px rgba(28,43,72,.05); }
    .order-detail-hero { padding: 24px 28px; }
    .eyebrow { color: #e21b23; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .detail-card { overflow: hidden; }
    .detail-card-header { padding: 17px 20px; border-bottom: 1px solid #e7ebf2; font-weight: 800; }
    .detail-card-body { padding: 20px; }
    .info-row { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #edf0f5; }
    .info-row:last-child { border-bottom: 0; }
    .info-row i { color: #e21b23; width: 18px; text-align: center; margin-top: 3px; }
    .info-label { color: #7b879b; font-size: .78rem; }
    .status-badge { border-radius: 999px; padding: 7px 11px; font-size: .76rem; font-weight: 700; }
    .status-waiting { background: #fff4d6; color: #986200; }
    .status-active { background: #e8f2ff; color: #2864a8; }
    .status-done { background: #e2f6eb; color: #177b4c; }
    .status-cancelled { background: #fde9eb; color: #a23945; }
    .payment-paid { color: #177b4c; background: #e2f6eb; }
    .payment-pending { color: #986200; background: #fff4d6; }
    .payment-failed { color: #a23945; background: #fde9eb; }
    .message-list { max-height: 340px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding-right: 6px; }
    .chat-message { display: flex; max-width: 82%; }
    .chat-message--customer { margin-left: auto; justify-content: flex-end; }
    .chat-message--admin { margin-right: auto; justify-content: flex-start; }
    .message { border-radius: 16px; padding: 11px 13px; font-size: .88rem; box-shadow: 0 8px 18px rgba(20, 33, 61, 0.05); word-break: break-word; }
    .message-admin { background: linear-gradient(135deg, #d71921 0%, #ef3d45 100%); color: #fff; }
    .message-customer { background: #f3f5f9; color: #172033; border: 1px solid #e7ebf2; }
    .message-meta { font-size: .72rem; opacity: .8; }
    .order-table th { color: #7b879b; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; }
    .order-table td, .order-table th { padding: 14px 16px; border-color: #edf0f5; }
</style>

<div class="order-detail-page py-4 py-lg-5">
    <div class="container">
        <div class="order-detail-hero mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div><div class="eyebrow">Order desk</div><h1 class="h3 fw-bold mb-1">Chi tiết đơn hàng #{{ $order->id }}</h1><div class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->customer_name }}</div><div class="small fw-semibold text-danger mt-1">{{ $order->ghn_order_code ?? $order->virtual_tracking_code }}</div></div>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách</a>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>@endif

        <div class="row g-4">
            <div class="col-xl-4">
                <div class="detail-card mb-4"><div class="detail-card-header"><i class="fa-solid fa-user me-2 text-danger"></i>Thông tin khách hàng</div><div class="detail-card-body">
                    <div class="info-row"><i class="fa-solid fa-user"></i><div><div class="info-label">Người nhận</div><strong>{{ $order->customer_name }}</strong></div></div>
                    <div class="info-row"><i class="fa-solid fa-phone"></i><div><div class="info-label">Số điện thoại</div><strong>{{ $order->customer_phone }}</strong></div></div>
                    <div class="info-row"><i class="fa-solid fa-location-dot"></i><div><div class="info-label">Địa chỉ giao hàng</div><span>{{ $order->customer_address }}</span></div></div>
                    @if($order->note)<div class="info-row"><i class="fa-solid fa-note-sticky"></i><div><div class="info-label">Ghi chú</div><span>{{ $order->note }}</span></div></div>@endif
                </div></div>

                <div class="detail-card"><div class="detail-card-header"><i class="fa-solid fa-sliders me-2 text-danger"></i>Cập nhật đơn hàng</div><div class="detail-card-body">
                    <div class="mb-3"><div class="info-label mb-2">Trạng thái hiện tại</div><span class="status-badge {{ $status === 'completed' ? 'status-done' : ($status === 'cancelled' ? 'status-cancelled' : ($status === 'pending_confirmation' ? 'status-waiting' : 'status-active')) }}">{{ $statusLabels[$status] ?? $order->status }}</span></div>
                    @if($status === 'cancelled')
                        <div class="alert alert-light border mb-0">Đơn đã hủy, không thể cập nhật sang trạng thái khác.</div>
                    @else
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">@csrf<label class="form-label small fw-bold">Chuyển trạng thái</label><div class="input-group"><select name="status" class="form-select"><option value="pending_confirmation" @selected($status === 'pending_confirmation')>Chờ xác nhận</option><option value="awaiting_pickup" @selected($status === 'awaiting_pickup')>Chờ lấy hàng</option><option value="awaiting_delivery" @selected($status === 'awaiting_delivery')>Chờ giao hàng</option><option value="in_transit" @selected($status === 'in_transit')>Đang giao</option><option value="completed" @selected($status === 'completed')>Hoàn thành</option>@if(!in_array($status, ['awaiting_delivery', 'in_transit', 'completed']))<option value="cancelled" @selected($status === 'cancelled')>Đã hủy</option>@endif</select><button class="btn btn-danger"><i class="fa-solid fa-check"></i></button></div></form>
                    @endif
                </div></div>
            </div>

            <div class="col-xl-8">
                <div class="detail-card mb-4"><div class="detail-card-header d-flex justify-content-between align-items-center"><span><i class="fa-solid fa-box-open me-2 text-danger"></i>Sản phẩm trong đơn</span><span class="text-muted small">{{ $order->items->sum('quantity') }} sản phẩm</span></div><div class="table-responsive"><table class="table order-table mb-0"><thead><tr><th>Sản phẩm</th><th class="text-center">Đơn giá</th><th class="text-center">SL</th><th class="text-end">Thành tiền</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td><strong>{{ $item->product_name }}</strong><div class="small text-muted">{{ $item->capacity_name ?? '' }}</div></td><td class="text-center">{{ number_format((float) $item->price, 0, ',', '.') }} đ</td><td class="text-center">{{ $item->quantity }}</td><td class="text-end fw-bold text-danger">{{ number_format((float) ($item->subtotal ?? ($item->price * $item->quantity)), 0, ',', '.') }} đ</td></tr>@endforeach</tbody></table></div><div class="detail-card-body border-top"><div class="d-flex justify-content-between mb-2"><span class="text-muted">Tạm tính</span><strong>{{ number_format($subtotal, 0, ',', '.') }} đ</strong></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Phí vận chuyển</span><strong>{{ number_format((float) ($order->shipping_fee ?? 0), 0, ',', '.') }} đ</strong></div>@if((float) $order->discount_amount > 0)<div class="d-flex justify-content-between mb-2 text-success"><span>Mã {{ $order->coupon_code }}</span><strong>-{{ number_format((float) $order->discount_amount, 0, ',', '.') }} đ</strong></div>@endif<div class="d-flex justify-content-between fs-5 text-danger fw-bold border-top pt-3"><span>Tổng thanh toán</span><span>{{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</span></div></div></div>

                <div class="row g-4"><div class="col-md-5"><div class="detail-card h-100"><div class="detail-card-header"><i class="fa-solid fa-wallet me-2 text-danger"></i>Thanh toán</div><div class="detail-card-body"><div class="info-label">Phương thức</div><strong class="d-block mb-3">{{ strtoupper($payment?->gateway ?? $order->payment_method ?? 'unknown') }}</strong><span class="status-badge {{ $payment?->status === 'paid' ? 'payment-paid' : ($payment?->status === 'failed' ? 'payment-failed' : 'payment-pending') }}">{{ $paymentLabel }}</span>@if($payment?->paid_at)<div class="small text-muted mt-3">{{ $payment->paid_at->format('d/m/Y H:i') }}</div>@endif</div></div></div><div class="col-md-7"><div class="detail-card h-100"><div class="detail-card-header"><i class="fa-solid fa-comments me-2 text-danger"></i>Phản hồi khách hàng</div><div class="detail-card-body"><div class="message-list">@forelse($order->messages as $message)<div class="chat-message {{ $message->sender_role === 'admin' ? 'chat-message--admin' : 'chat-message--customer' }}"><div class="message {{ $message->sender_role === 'admin' ? 'message-admin' : 'message-customer' }}"><div class="small fw-semibold mb-1">{{ $message->sender_role === 'admin' ? 'Admin' : 'Khách hàng' }}</div><div>{{ $message->message }}</div><div class="message-meta mt-1 {{ $message->sender_role === 'admin' ? 'text-white-50' : 'text-muted' }}">{{ $message->created_at->format('d/m H:i') }}</div></div></div>@empty<div class="small text-muted py-2">Chưa có tin nhắn.</div>@endforelse</div><form action="{{ route('admin.orders.sendMessage', $order->id) }}" method="POST" class="input-group mt-3">@csrf<input type="text" name="message" class="form-control" maxlength="1000" placeholder="Viết phản hồi cho khách..." required><button class="btn btn-danger"><i class="fa-solid fa-paper-plane"></i></button></form></div></div></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
