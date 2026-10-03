@extends('layouts.shop')

@section('content')
<div class="container py-5">
    <div class="mx-auto p-4 p-md-5 bg-white border shadow-sm text-center" style="max-width: 520px;">
        <div class="text-danger mb-3"><i class="fa-solid fa-qrcode fa-3x"></i></div>
        <h1 class="h4 fw-bold mb-2">Quét mã QR MoMo</h1>
        <p class="text-muted mb-1">Đơn hàng #{{ $order->id }}</p>
        <p class="fs-4 fw-bold text-danger">{{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</p>

        <div class="my-4 p-3 border rounded bg-white d-inline-block">
            <img src="{{ $qrCodeUrl }}" alt="Mã QR thanh toán MoMo cho đơn #{{ $order->id }}" class="img-fluid" style="width: 260px; height: 260px; object-fit: contain;">
        </div>

        <p class="small text-muted">Mở ứng dụng MoMo và quét mã để hoàn tất thanh toán.</p>
        <div class="d-flex justify-content-center gap-2 mt-4">
            @if($payUrl)<a href="{{ $payUrl }}" class="btn btn-danger"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Mở MoMo</a>@endif
            <a href="{{ route('user.orders.show', $order) }}" class="btn btn-outline-secondary">Xem đơn hàng</a>
        </div>
    </div>
</div>
@endsection
