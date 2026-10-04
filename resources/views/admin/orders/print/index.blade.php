@extends('layouts.shop')

@section('content')
<style>
    .print-manager { background:#f5f7fb; min-height:calc(100vh - 72px); }
    .print-card { background:#fff; border:1px solid #e7ebf2; border-radius:16px; box-shadow:0 8px 24px rgba(28,43,72,.04); }
    .print-card .table { min-width:900px; }
    .print-card th { color:#7b879b; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; }
    .quick-filter { border:1px solid #e1e6ef; border-radius:10px; padding:10px 14px; color:#26334d; text-decoration:none; background:#fff; }
    .quick-filter:hover, .quick-filter.active { border-color:#e21b23; color:#e21b23; }
</style>
<div class="print-manager py-4 py-lg-5">
    <div class="container" style="max-width:1440px">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div><div class="text-danger small fw-bold text-uppercase">Order printing</div><h1 class="h3 mb-1">Quản lý in đơn hàng</h1><p class="text-muted mb-0">Lọc theo ngày, trạng thái và theo dõi các đơn đã in.</p></div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Quản lý đơn</a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <a class="quick-filter {{ request('printed') === 'unprinted' ? 'active' : '' }}" href="{{ route('admin.orders.print.unprinted', request()->except('printed')) }}"><i class="fa-regular fa-circle me-1"></i>Chưa in</a>
            <a class="quick-filter {{ request('printed') === 'printed' ? 'active' : '' }}" href="{{ route('admin.orders.print.printed', request()->except('printed')) }}"><i class="fa-solid fa-check me-1"></i>Đã in / in lại</a>
            <a class="quick-filter {{ !request('printed') ? 'active' : '' }}" href="{{ route('admin.orders.print.index') }}"><i class="fa-solid fa-layer-group me-1"></i>Tất cả</a>
        </div>

        <div class="print-card p-3 mb-4">
            <form method="GET" action="{{ route('admin.orders.print.index') }}" class="row g-2">
                <div class="col-lg-3"><input name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Mã đơn, khách hàng, điện thoại"></div>
                <div class="col-md-3 col-lg-2"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="Từ ngày"></div>
                <div class="col-md-3 col-lg-2"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="Đến ngày"></div>
                <div class="col-md-3 col-lg-2"><select name="status" class="form-select"><option value="">Mọi trạng thái</option>@foreach($statusOptions as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-3 col-lg-1"><select name="payment_method" class="form-select"><option value="">Trả</option><option value="cod" @selected(request('payment_method') === 'cod')>COD</option><option value="visa" @selected(request('payment_method') === 'visa')>Visa</option><option value="domestic" @selected(request('payment_method') === 'domestic')>Nội địa</option></select></div>
                <div class="col-md-3 col-lg-1"><select name="payment_status" class="form-select"><option value="">TT</option><option value="paid" @selected(request('payment_status') === 'paid')>Đã trả</option><option value="pending" @selected(request('payment_status') === 'pending')>Chưa trả</option></select></div>
                <div class="col-md-3 col-lg-1"><button class="btn btn-danger w-100" type="submit"><i class="fa-solid fa-filter"></i></button></div>
                <input type="hidden" name="printed" value="{{ request('printed') }}">
            </form>
        </div>

        <form method="POST" action="{{ route('admin.orders.print.bulk') }}" id="bulk-print-form">
            @csrf
            <div class="print-card overflow-hidden">
                <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div><strong>{{ $orders->count() }} đơn phù hợp</strong><div class="small text-muted">Chọn đơn để in cùng một lần.</div></div>
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-print me-2"></i>In các đơn đã chọn</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th class="ps-3"><input type="checkbox" id="select-all" aria-label="Chọn tất cả"></th><th>Đơn hàng</th><th>Khách hàng</th><th>Trạng thái</th><th>Thanh toán</th><th>Lần in gần nhất</th><th class="text-end">Thao tác</th></tr></thead>
                        <tbody>
                        @forelse($orders as $order)
                            @php $lastPrint = $order->printHistories->first(); @endphp
                            <tr>
                                <td class="ps-3"><input class="order-check" type="checkbox" name="order_ids[]" value="{{ $order->id }}"></td>
                                <td><strong>#{{ $order->id }}</strong><div class="small text-muted">{{ optional($order->created_at)->format('d/m/Y H:i') }}</div></td>
                                <td>{{ $order->customer_name }}<div class="small text-muted">{{ $order->customer_phone }}</div></td>
                                <td>{{ $order->status_label }}</td>
                                <td>{{ strtoupper($order->payment_method ?: 'N/A') }}</td>
                                <td>@if($lastPrint)<span class="text-success">{{ optional($lastPrint->printed_at)->format('d/m/Y H:i') }}</span><div class="small text-muted">{{ $lastPrint->print_type === 'bulk' ? 'Hàng loạt' : 'Một đơn' }}</div>@else<span class="text-warning">Chưa in</span>@endif</td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-danger" target="_blank" href="{{ route('admin.orders.print.one', $order) }}"><i class="fa-solid fa-print me-1"></i>{{ $lastPrint ? 'In lại' : 'In đơn' }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">Không có đơn hàng phù hợp.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('select-all')?.addEventListener('change', function () {
    document.querySelectorAll('.order-check').forEach((checkbox) => { checkbox.checked = this.checked; });
});
document.getElementById('bulk-print-form')?.addEventListener('submit', function (event) {
    if (!document.querySelector('.order-check:checked')) { event.preventDefault(); alert('Vui lòng chọn ít nhất một đơn hàng để in.'); }
});
</script>
@endsection
