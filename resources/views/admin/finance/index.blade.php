@extends('layouts.shop')

@section('content')
<div class="container py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Thống kê tài chính</h2>
            <p class="text-muted mb-0">Tổng hợp theo từng đơn hàng, không đếm trùng các lần thanh toán.</p>
        </div>
        <a href="{{ route('admin.finance.transactions', request()->query()) }}" class="btn btn-outline-danger">
            <i class="fa-solid fa-list me-1"></i> Giao dịch thanh toán
        </a>
    </div>

    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Mã đơn, tên hoặc số điện thoại</label><input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Từ ngày</label><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Đến ngày</label><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Tối thiểu</label><input type="number" min="0" name="min_amount" value="{{ $filters['min_amount'] ?? '' }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Tối đa</label><input type="number" min="0" name="max_amount" value="{{ $filters['max_amount'] ?? '' }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Phương thức</label><select name="gateway" class="form-select"><option value="">Tất cả</option>@foreach($methods as $key => $label)<option value="{{ $key }}" @selected(($filters['gateway'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Trạng thái thanh toán</label><select name="payment_status" class="form-select"><option value="">Tất cả</option>@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected(($filters['payment_status'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-6 d-flex align-items-end gap-2"><button class="btn btn-danger"><i class="fa-solid fa-filter me-1"></i>Áp dụng</button><a href="{{ route('admin.finance.index') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a></div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted">Tổng giá trị đơn</div><div class="fs-4 fw-bold">{{ number_format((float) $summary->total_amount, 0, ',', '.') }} đ</div><small>{{ $summary->order_count }} đơn</small></div></div></div>
        @foreach($statuses as $key => $label)
            @php($total = $statusTotals->get($key))
            <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted">{{ $label }}</div><div class="fs-4 fw-bold">{{ number_format((float) ($total->total_amount ?? 0), 0, ',', '.') }} đ</div><small>{{ $total->order_count ?? 0 }} đơn</small></div></div></div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Thống kê theo phương thức</div>
        <div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Phương thức</th><th>Số đơn</th><th>Tổng giá trị</th><th>Đã thanh toán</th></tr></thead><tbody>
            @forelse($methodTotals as $key => $total)
                @php($label = $methods[$key])
                <tr><td>{{ $label }}</td><td>{{ $total->order_count ?? 0 }}</td><td>{{ number_format((float) ($total->total_amount ?? 0), 0, ',', '.') }} đ</td><td>{{ number_format((float) ($total->paid_amount ?? 0), 0, ',', '.') }} đ</td></tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Không có dữ liệu phương thức thanh toán trong bộ lọc này.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
</div>
@endsection