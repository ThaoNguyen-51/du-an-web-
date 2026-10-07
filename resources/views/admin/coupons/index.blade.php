@extends('layouts.shop')

@section('content')
<style>
    .coupons-page { --coupon-ink: #172033; --coupon-muted: #718096; --coupon-line: #e5e9f0; color: var(--coupon-ink); }
    .coupons-heading { padding-bottom: 18px; border-bottom: 1px solid var(--coupon-line); margin-bottom: 20px; }
    .coupons-heading h1 { font-size: 1.45rem; font-weight: 800; margin: 0; }
    .coupons-heading p { color: var(--coupon-muted); margin: 6px 0 0; font-size: .88rem; }
    .coupon-panel { background: #fff; border: 1px solid var(--coupon-line); border-radius: 8px; }
    .coupon-panel-title { padding: 14px 18px; border-bottom: 1px solid var(--coupon-line); font-weight: 750; }
    .coupon-create { padding: 18px; }
    .coupon-table { min-width: 1360px; }
    .coupon-table th { color: var(--coupon-muted); background: #f8f9fb; font-size: .7rem; text-transform: uppercase; white-space: nowrap; }
    .coupon-table td, .coupon-table th { padding: 10px; vertical-align: middle; }
    .coupon-row-form { display: grid; grid-template-columns: 100px 105px 90px 95px 90px 160px 160px 75px 75px auto; gap: 6px; align-items: center; }
    .coupon-row-form .form-control, .coupon-row-form .form-select { min-width: 0; font-size: .75rem; padding: 5px 7px; }
    .coupon-active { display: flex; gap: 5px; align-items: center; font-size: .72rem; white-space: nowrap; }
</style>

<div class="coupons-page">
    <header class="coupons-heading">
        <h1>Quản lý mã giảm giá</h1>
        <p>Tạo mã giảm theo số tiền hoặc phần trăm; cấu hình giá trị đơn tối thiểu, thời gian và lượt sử dụng.</p>
    </header>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <section class="coupon-panel mb-4">
        <div class="coupon-panel-title"><i class="fa-solid fa-ticket text-danger me-2"></i>Tạo mã giảm giá</div>
        <form action="{{ route('admin.coupons.store') }}" method="POST" class="coupon-create">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="new-code">Mã</label><input id="new-code" name="code" class="form-control text-uppercase" maxlength="40" value="{{ old('code') }}" required></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="new-type">Loại giảm</label><select id="new-type" name="discount_type" class="form-select"><option value="fixed">Số tiền</option><option value="percentage">Phần trăm</option></select></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="new-value">Mức giảm</label><input id="new-value" name="discount_value" type="number" min="1" step="1" class="form-control" required></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="new-minimum">Đơn tối thiểu</label><input id="new-minimum" name="minimum_order" type="number" min="0" step="1000" value="0" class="form-control" required></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="new-cap">Giảm tối đa</label><input id="new-cap" name="maximum_discount" type="number" min="1" step="any" class="form-control" placeholder="Không giới hạn"></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label" for="new-limit">Lượt dùng</label><input id="new-limit" name="usage_limit" type="number" min="1" class="form-control" placeholder="Không giới hạn"></div>
                <div class="col-sm-6 col-lg-3"><label class="form-label" for="new-start">Bắt đầu</label><input id="new-start" name="starts_at" type="datetime-local" class="form-control"></div>
                <div class="col-sm-6 col-lg-3"><label class="form-label" for="new-expiry">Kết thúc</label><input id="new-expiry" name="expires_at" type="datetime-local" class="form-control"></div>
                <div class="col-sm-6 col-lg-2"><label class="form-label d-block">Trạng thái</label><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><span class="form-check-label">Đang hoạt động</span></label></div>
                <div class="col-sm-6 col-lg-4 d-grid"><button class="btn btn-danger"><i class="fa-solid fa-plus me-1"></i>Tạo mã</button></div>
            </div>
        </form>
    </section>

    <section class="coupon-panel">
        <div class="coupon-panel-title d-flex justify-content-between align-items-center"><span>Danh sách mã</span><span class="badge bg-light text-dark border">{{ $coupons->total() }}</span></div>
        @if($coupons->isEmpty())
            <div class="text-center text-muted py-5">Chưa có mã giảm giá.</div>
        @else
            <div class="table-responsive">
                <table class="table coupon-table mb-0">
                    <thead><tr><th>Mã giảm</th><th>Loại</th><th>Mức giảm</th><th>Đơn tối thiểu</th><th>Giảm tối đa</th><th>Bắt đầu</th><th>Kết thúc</th><th>Đã dùng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                            <tr>
                                <td colspan="9">
                                    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="coupon-row-form">
                                        @csrf
                                        @method('PUT')
                                        <input name="code" value="{{ $coupon->code }}" class="form-control text-uppercase" aria-label="Mã">
                                        <select name="discount_type" class="form-select" aria-label="Loại giảm"><option value="fixed" @selected($coupon->discount_type === 'fixed')>Số tiền</option><option value="percentage" @selected($coupon->discount_type === 'percentage')>Phần trăm</option></select>
                                        <input name="discount_value" type="number" min="1" step="1" value="{{ $coupon->discount_value }}" class="form-control" aria-label="Mức giảm">
                                        <input name="minimum_order" type="number" min="0" step="1000" value="{{ $coupon->minimum_order }}" class="form-control" aria-label="Đơn tối thiểu">
                                        <input name="maximum_discount" type="number" min="1" step="any" value="{{ $coupon->maximum_discount }}" class="form-control" aria-label="Giảm tối đa" placeholder="Không giới hạn">
                                        <input name="starts_at" type="datetime-local" value="{{ $coupon->starts_at?->format('Y-m-d\\TH:i') }}" class="form-control" aria-label="Bắt đầu">
                                        <input name="expires_at" type="datetime-local" value="{{ $coupon->expires_at?->format('Y-m-d\\TH:i') }}" class="form-control" aria-label="Kết thúc">
                                        <input name="usage_limit" type="number" min="1" value="{{ $coupon->usage_limit }}" class="form-control" aria-label="Giới hạn lượt">
                                        <label class="coupon-active"><input type="checkbox" name="is_active" value="1" @checked($coupon->is_active)> Bật</label>
                                        <button class="btn btn-sm btn-outline-primary">Lưu</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <span class="d-block small text-muted mb-1">{{ $coupon->used_count }}/{{ $coupon->usage_limit ?? '∞' }}</span>
                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Xóa mã giảm giá này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Xóa mã" aria-label="Xóa mã"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($coupons->hasPages())<div class="p-3">{{ $coupons->links('pagination::bootstrap-5') }}</div>@endif
        @endif
    </section>
</div>
@endsection
