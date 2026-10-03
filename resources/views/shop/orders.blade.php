@extends('layouts.shop')

@section('content')
<style>
    .bg-hc { background-color: #d71921 !important; }
    .text-hc { color: #d71921 !important; }
    .btn-hc { background-color: #d71921; color: #fff; border: none; }
    .btn-hc:hover { background-color: #b51219; color: #fff; }
    
    .nav-pills .nav-link.active {
        background-color: #d71921 !important;
        color: #fff !important;
    }
    .nav-pills .nav-link {
        color: #495057;
        font-weight: 500;
    }
</style>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="fa-solid fa-clock-rotate-left me-2 text-hc"></i>Lịch Sử Đơn Hàng</h3>
        <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-cart-shopping me-1"></i> Tiếp tục mua sắm
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-0 fw-bold">Tìm kiếm đơn hàng</h5>
                </div>
                <i class="fa-solid fa-magnifying-glass-chart text-hc fs-4"></i>
            </div>

            <form action="{{ route('user.orders.index') }}" method="GET" class="row g-2 align-items-center">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fa-solid fa-search text-muted"></i>
                        </span>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control border-start-0" placeholder="Tìm theo mã đơn, mã vận đơn, tên khách, tin nhắn...">
                    </div>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-hc flex-fill">
                        <i class="fa-solid fa-filter me-1"></i>Tìm kiếm
                    </button>
                    @if(request('keyword') || request('status'))
                        <a href="{{ route('user.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Thanh Bộ Lọc Trạng Thái Đơn Hàng -->
    <div class="bg-white p-2 rounded shadow-sm mb-4 border">
        <ul class="nav nav-pills nav-fill flex-column flex-sm-row gap-1">
            <li class="nav-item">
                <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('user.orders.index') }}">Tất cả</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'pending_confirmation' ? 'active' : '' }}" href="{{ route('user.orders.index', ['status' => 'pending_confirmation']) }}">Chờ xác nhận</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'awaiting_pickup' ? 'active' : '' }}" href="{{ route('user.orders.index', ['status' => 'awaiting_pickup']) }}">Chờ lấy hàng</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'awaiting_delivery' ? 'active' : '' }}" href="{{ route('user.orders.index', ['status' => 'awaiting_delivery']) }}">Chờ giao hàng</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'in_transit' ? 'active' : '' }}" href="{{ route('user.orders.index', ['status' => 'in_transit']) }}">Đang giao</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'completed' ? 'active' : '' }}" href="{{ route('user.orders.index', ['status' => 'completed']) }}">Đã hoàn thành</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'cancelled' ? 'active' : '' }}" href="{{ route('user.orders.index', ['status' => 'cancelled']) }}">Đã hủy</a>
            </li>
        </ul>
    </div>

    @if($orders->isEmpty())
        <div class="text-center py-5 bg-white rounded shadow-sm border">
            <i class="fa-solid fa-box-open display-1 text-muted mb-3"></i>
            <p class="fs-5 text-muted fw-bold">Không tìm thấy đơn hàng nào!</p>
            <a href="{{ route('shop.index') }}" class="btn btn-hc px-4 fs-6 fw-bold mt-2">
                <i class="fa-solid fa-cart-shopping me-2"></i>Mua sắm ngay
            </a>
        </div>
    @else
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Mã vận đơn</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Phí ship</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            @php
                                $lastTransaction = $order->paymentTransactions
                                    ->sortByDesc('id')
                                    ->sortByDesc(fn ($transaction) => in_array($transaction->status, ['paid', 'refund_pending', 'refunded'], true))
                                    ->first();
                                $paymentGateway = $lastTransaction?->gateway ?? $order->payment_method ?? 'unknown';
                                // Mapping Badge Trạng Thái
                                $statusMap = [
                                    'pending_confirmation' => ['label' => 'Chờ xác nhận', 'class' => 'bg-warning text-dark'],
                                    'awaiting_pickup' => ['label' => 'Chờ lấy hàng', 'class' => 'bg-info text-dark'],
                                    'awaiting_delivery' => ['label' => 'Chờ giao hàng', 'class' => 'bg-primary'],
                                    'in_transit' => ['label' => 'Đang giao', 'class' => 'bg-success'],
                                    'completed' => ['label' => 'Đã hoàn thành', 'class' => 'bg-success'],
                                    'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-danger'],
                                    'pending' => ['label' => 'Chờ xác nhận', 'class' => 'bg-warning text-dark'],
                                ];
                                $st = $statusMap[\App\Models\Order::normalizeStatus($order->status)] ?? ['label' => $order->status_label ?? 'Khác', 'class' => 'bg-secondary'];

                                $trackingCode = $order->ghn_order_code ?? $order->shipping_code ?? $order->virtual_tracking_code ?? null;
                                $normalizedOrderStatus = \App\Models\Order::normalizeStatus($order->status);
                                $canCustomerCancel = $order->payment_method === 'cod'
                                    && in_array($normalizedOrderStatus, ['pending_confirmation', 'awaiting_pickup'], true)
                                    && !$order->ghn_order_code
                                    && !in_array($order->shipping_status, ['delivering', 'delivered', 'cancelled'], true);
                            @endphp
                            <tr>
                                <td class="fw-bold">#{{ $order->id }}</td>
                                
                                <td>
                                    @if($trackingCode)
                                        <span class="badge bg-light text-dark border fw-normal">
                                            {{ $trackingCode }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Chưa có mã vận đơn</span>
                                    @endif
                                </td>

                                <td>{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td class="fw-bold text-hc">{{ number_format($order->total_amount ?? 0, 0, ',', '.') }} đ</td>
                                
                                <td>{{ number_format($order->ghn_total_fee ?? $order->shipping_fee ?? 0, 0, ',', '.') }} đ</td>

                                <td>
                                    @if($lastTransaction?->status === 'paid')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fa-solid fa-check me-1"></i>Đã thanh toán
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            Chưa thanh toán
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge {{ $st['class'] }}">{{ $st['label'] }}</span>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                        <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-sm btn-outline-danger fw-semibold">
                                            <i class="fa-solid fa-eye me-1"></i>Chi tiết
                                        </a>
                                        @if($normalizedOrderStatus === \App\Models\Order::STATUS_COMPLETED)
                                            <a href="{{ route('user.orders.show', $order->id) }}#review-order-items" class="btn btn-sm btn-outline-warning fw-semibold">
                                                <i class="fa-solid fa-star me-1"></i>Đánh giá
                                            </a>
                                        @endif
                                        @if($canCustomerCancel)
                                            <form action="{{ route('user.orders.cancel', $order) }}" method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc muốn hủy đơn COD này?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary fw-semibold"><i class="fa-solid fa-ban me-1"></i>Hủy đơn</button>
                                            </form>
                                        @endif
                                        @if(\App\Models\Order::isOnlinePaymentMethod($order->payment_method) && $lastTransaction?->status !== 'paid' && !$order->ghn_order_code)
                                            <a href="{{ route('payment.momo.pay', $order) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                                                <i class="fa-solid fa-rotate-right me-1"></i>Thanh toán lại
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($orders->hasPages())
                <div class="mt-3 d-flex justify-content-end">
                    {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection