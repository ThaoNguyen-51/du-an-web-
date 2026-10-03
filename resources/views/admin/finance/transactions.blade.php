@extends('layouts.shop')

@section('content')
<div class="container py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div><h2 class="fw-bold mb-1">Giao dịch thanh toán</h2><p class="text-muted mb-0">Tra cứu theo đơn hàng và cập nhật thủ công trạng thái COD.</p></div>
        <a href="{{ route('admin.finance.index', request()->except('sort', 'page')) }}" class="btn btn-outline-danger"><i class="fa-solid fa-chart-column me-1"></i>Thống kê tài chính</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="GET" class="card border-0 shadow-sm mb-4"><div class="card-body"><div class="row g-3">
        <div class="col-md-4"><label class="form-label">Mã đơn, tên hoặc số điện thoại</label><input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Từ ngày</label><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Đến ngày</label><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Tối thiểu</label><input type="number" min="0" name="min_amount" value="{{ $filters['min_amount'] ?? '' }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Tối đa</label><input type="number" min="0" name="max_amount" value="{{ $filters['max_amount'] ?? '' }}" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Phương thức</label><select name="gateway" class="form-select"><option value="">Tất cả</option>@foreach($methods as $key => $label)<option value="{{ $key }}" @selected(($filters['gateway'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Trạng thái</label><select name="payment_status" class="form-select"><option value="">Tất cả</option>@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected(($filters['payment_status'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Sắp xếp</label><select name="sort" class="form-select"><option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Mới nhất</option><option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Cũ nhất</option><option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>Số tiền tăng dần</option><option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>Số tiền giảm dần</option></select></div>
        <div class="col-md-3 d-flex align-items-end gap-2"><button class="btn btn-danger">Áp dụng</button><a href="{{ route('admin.finance.transactions') }}" class="btn btn-outline-secondary">Xóa</a></div>
    </div></div></form>

    <div class="card border-0 shadow-sm"><div class="card-header bg-white"><strong>Danh sách giao dịch ({{ $orders->total() }})</strong><div class="small text-muted">Mỗi đơn chỉ hiển thị một giao dịch đại diện. Chỉ đơn COD được cập nhật thủ công.</div></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Đơn hàng</th><th>Khách hàng</th><th>Phương thức</th><th>Số tiền</th><th>Thanh toán</th><th>Cập nhật COD</th></tr></thead><tbody>
        @forelse($orders as $order)
            @php($isCod = $order->gateway === 'cod')
            @php($createdAt = \Carbon\Carbon::parse($order->created_at))
            <tr><td><strong>#{{ $order->id }}</strong><div class="small text-muted">{{ $createdAt->format('d/m/Y H:i') }}</div></td><td>{{ $order->customer_name }}<div class="small text-muted">{{ $order->customer_phone }}</div></td><td>{{ $methods[$order->gateway] ?? $order->gateway }}</td><td>{{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</td><td><span class="badge text-bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">{{ $statuses[$order->payment_status] ?? $order->payment_status }}</span></td><td>
                @if($isCod && isset($codTransitions[$order->payment_status]))
                    <form method="POST" action="{{ route('admin.finance.update-status', $order->id) }}" class="d-flex gap-2">@csrf @method('PATCH')<input type="hidden" name="current_payment_status" value="{{ $order->payment_status }}"><input type="hidden" name="current_order_status" value="{{ $order->status }}"><input type="hidden" name="current_payment_id" value="{{ $order->payment_id ?? 0 }}"><select name="payment_status" class="form-select form-select-sm">@foreach($codTransitions[$order->payment_status] as $next)<option value="{{ $next }}" @selected($next === $order->payment_status)>{{ $statuses[$next] }}</option>@endforeach</select><button class="btn btn-sm btn-danger" title="Lưu trạng thái"><i class="fa-solid fa-save"></i></button></form>
                @else<span class="text-muted small">Không áp dụng</span>@endif
            </td></tr>
        @empty<tr><td colspan="6" class="text-center text-muted py-4">Không có giao dịch phù hợp với bộ lọc.</td></tr>@endforelse
        </tbody></table></div>
        @if($orders->hasPages())<div class="card-footer bg-white">{{ $orders->links('pagination::bootstrap-5') }}</div>@endif
    </div>
</div>
@endsection