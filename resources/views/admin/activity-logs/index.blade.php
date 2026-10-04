@extends('layouts.shop')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Nhật ký hoạt động</h3><p class="text-muted mb-0">Theo dõi thay đổi quan trọng trên sản phẩm và đơn hàng.</p></div>
</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <form class="row g-2">
        <div class="col-md-5"><input name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Tìm nội dung nhật ký"></div>
        <div class="col-md-4"><select name="action" class="form-select">
            <option value="">Tất cả sự kiện</option>
            @foreach(['product_created'=>'Tạo sản phẩm','product_updated'=>'Sửa sản phẩm','price_changed'=>'Sửa giá','stock_changed'=>'Sửa tồn kho','product_deleted'=>'Xóa sản phẩm','order_status_changed'=>'Đổi trạng thái đơn'] as $key => $label)
                <option value="{{ $key }}" @selected(request('action') === $key)>{{ $label }}</option>
            @endforeach
        </select></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-secondary flex-grow-1">Lọc</button><a href="{{ route('admin.activity-logs.index') }}" class="btn btn-light border">Xóa</a></div>
    </form>
</div></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Thời gian</th><th>Sự kiện</th><th>Nội dung</th><th>Người thực hiện</th></tr></thead>
    <tbody>@forelse($logs as $log)<tr>
        <td class="text-muted small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
        <td><span class="badge bg-primary-subtle text-primary">{{ str_replace('_', ' ', $log->action) }}</span></td>
        <td>{{ $log->summary }} @if($log->properties)<small class="d-block text-muted">{{ collect($log->properties)->map(fn($v, $k) => $k . ': ' . (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v))->join(' · ') }}</small>@endif</td>
        <td>{{ $log->actor?->name ?? 'Hệ thống' }}</td>
    </tr>@empty<tr><td colspan="4" class="text-center text-muted py-5">Chưa có hoạt động nào.</td></tr>@endforelse</tbody>
</table></div><div class="p-3">{{ $logs->links() }}</div></div>
@endsection
