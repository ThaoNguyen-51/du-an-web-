@extends('layouts.shop')

@section('content')
<style>
    .customers-page { --customers-ink: #172033; --customers-muted: #718096; --customers-line: #e5e9f0; color: var(--customers-ink); }
    .customers-heading { padding-bottom: 18px; border-bottom: 1px solid var(--customers-line); margin-bottom: 20px; }
    .customers-heading h1 { font-size: 1.45rem; font-weight: 800; margin: 0; }
    .customers-heading p { color: var(--customers-muted); margin: 6px 0 0; font-size: .88rem; }
    .customers-panel { background: #fff; border: 1px solid var(--customers-line); border-radius: 8px; }
    .customers-panel-head { padding: 15px 18px; border-bottom: 1px solid var(--customers-line); }
    .customers-table { min-width: 850px; }
    .customers-table th { background: #f8f9fb; color: var(--customers-muted); font-size: .72rem; text-transform: uppercase; white-space: nowrap; }
    .customers-table td, .customers-table th { padding: 12px 14px; vertical-align: middle; }
    .customer-edit-form { display: grid; grid-template-columns: 1fr 1.2fr 1fr 1fr auto; gap: 7px; align-items: center; }
    .customer-edit-form .form-control { min-width: 0; font-size: .8rem; }
    @media (max-width: 767.98px) { .customer-edit-form { grid-template-columns: 1fr; } }
</style>

<div class="customers-page">
    <header class="customers-heading">
        <h1>Quản lý khách hàng</h1>
        <p>Tìm khách theo tên, email, số điện thoại hoặc đơn hàng; cập nhật tài khoản mà không xóa lịch sử mua hàng.</p>
    </header>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <section class="customers-panel">
        <div class="customers-panel-head d-flex flex-wrap justify-content-between align-items-center gap-3">
            <strong>Danh sách tài khoản khách hàng</strong>
            <form action="{{ route('admin.customers.index') }}" method="GET" class="d-flex gap-2">
                <input type="search" name="keyword" value="{{ $keyword }}" class="form-control form-control-sm" placeholder="Tên, email, SĐT, mã đơn..." aria-label="Tìm khách hàng">
                <button class="btn btn-sm btn-outline-secondary" type="submit" title="Tìm kiếm"><i class="fa-solid fa-magnifying-glass"></i></button>
                @if($keyword !== '')<a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light border" title="Xóa tìm kiếm"><i class="fa-solid fa-xmark"></i></a>@endif
            </form>
        </div>

        @if($customers->isEmpty())
            <div class="text-center text-muted py-5">Không tìm thấy khách hàng.</div>
        @else
            <div class="table-responsive">
                <table class="table customers-table mb-0">
                    <thead><tr><th>Khách hàng</th><th>Hoạt động</th><th>Cập nhật tài khoản</th></tr></thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td><strong class="d-block">{{ $customer->name }}</strong><small class="text-muted">{{ $customer->email }}</small></td>
                                <td><span class="badge bg-light text-dark border">{{ $customer->orders_count }} đơn hàng</span><small class="text-muted d-block mt-1">{{ $customer->email_verified_at ? 'Đã xác thực email' : 'Chưa xác thực email' }}</small></td>
                                <td>
                                    <form action="{{ route('admin.customers.update', ['customer' => $customer->id, 'keyword' => $keyword]) }}" method="POST" class="customer-edit-form">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="keyword" value="{{ $keyword }}">
                                        <input name="name" value="{{ $customer->name }}" class="form-control" aria-label="Tên khách hàng" required>
                                        <input name="email" type="email" value="{{ $customer->email }}" class="form-control" aria-label="Email khách hàng" required>
                                        <input name="password" type="password" minlength="8" class="form-control" aria-label="Mật khẩu mới, không bắt buộc" placeholder="Mật khẩu mới">
                                        <input name="password_confirmation" type="password" minlength="8" class="form-control" aria-label="Xác nhận mật khẩu mới" placeholder="Xác nhận mật khẩu">
                                        <button class="btn btn-sm btn-outline-primary">Lưu</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($customers->hasPages())<div class="p-3">{{ $customers->links('pagination::bootstrap-5') }}</div>@endif
        @endif
    </section>
</div>
@endsection
