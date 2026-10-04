@extends('layouts.shop')

@section('content')
<style>
    .customers-page { --customers-ink: #172033; --customers-muted: #718096; --customers-line: #e5e9f0; --customers-red: #e21b23; color: var(--customers-ink); }
    .customers-heading { display:flex; justify-content:space-between; align-items:flex-end; gap:20px; padding: 4px 0 24px; border-bottom: 1px solid var(--customers-line); margin-bottom: 22px; }
    .customers-heading h1 { font-size: 1.7rem; font-weight: 800; letter-spacing: -.03em; margin: 0; }
    .customers-heading p { color: var(--customers-muted); margin: 8px 0 0; font-size: .9rem; max-width: 720px; }
    .customers-date { display:inline-flex; align-items:center; gap:8px; color:var(--customers-muted); font-size:.82rem; white-space:nowrap; }
    .customers-date i { color:var(--customers-red); }
    .customers-panel { background: #fff; border: 1px solid var(--customers-line); border-radius: 14px; box-shadow: 0 10px 30px rgba(23,32,51,.05); overflow:hidden; }
    .customers-panel-head { padding: 20px 22px; border-bottom: 1px solid var(--customers-line); }
    .customers-panel-title { font-size:1rem; font-weight:800; }
    .customers-panel-subtitle { color:var(--customers-muted); font-size:.78rem; margin-top:3px; }
    .customers-search { width:min(360px, 100%); }
    .customers-search .form-control { border-right:0; }
    .customers-search .btn { border-color:#ced5df; }
    .customers-table { min-width: 980px; }
    .customers-table th { background: #f7f9fc; color: var(--customers-muted); font-size: .7rem; font-weight:800; letter-spacing:.06em; text-transform: uppercase; white-space: nowrap; border-bottom:1px solid var(--customers-line); }
    .customers-table td, .customers-table th { padding: 16px 18px; vertical-align: middle; }
    .customers-table tbody tr { transition: background .15s ease; }
    .customers-table tbody tr:hover { background:#fbfcfe; }
    .customer-identity { display:flex; align-items:center; gap:12px; min-width:220px; }
    .customer-avatar { width:42px; height:42px; flex:0 0 42px; display:grid; place-items:center; border-radius:12px; background:#fff0f1; color:var(--customers-red); font-weight:800; font-size:1.05rem; }
    .customer-name { font-weight:800; color:var(--customers-ink); }
    .customer-email { color:var(--customers-muted); font-size:.8rem; margin-top:3px; }
    .customer-activity { min-width:150px; }
    .customer-order-count { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:7px; background:#f1f5f9; color:#344054; font-size:.76rem; font-weight:700; }
    .customer-verified { display:flex; align-items:center; gap:5px; margin-top:8px; font-size:.75rem; }
    .customer-verified.is-verified { color:#16845b; }
    .customer-verified.is-pending { color:#b7791f; }
    .customer-edit-form { display: grid; grid-template-columns: 1fr 1.35fr 1fr 1fr auto; gap: 8px; align-items: center; }
    .customer-edit-form .form-control { min-width: 0; font-size: .8rem; border-color:#dfe4ec; min-height:38px; }
    .customer-edit-form .form-control:focus { border-color:var(--customers-red); box-shadow:0 0 0 .18rem rgba(226,27,35,.1); }
    .customer-edit-form .btn { min-height:38px; padding-left:15px; padding-right:15px; }
    .customers-empty { padding:70px 20px; }
    .customers-empty i { color:#cbd5e1; font-size:2.8rem; }
    @media (max-width: 900px) { .customers-heading { align-items:flex-start; flex-direction:column; } .customer-edit-form { grid-template-columns: 1fr 1fr; } .customer-edit-form .btn { width:100%; } }
    @media (max-width: 575.98px) { .customers-panel-head { padding:16px; } .customers-search { width:100%; } .customer-edit-form { grid-template-columns:1fr; } }
</style>

<div class="customers-page">
    <header class="customers-heading">
        <div>
            <h1>Quản lý khách hàng</h1>
            <p>Tìm kiếm, cập nhật thông tin tài khoản và theo dõi hoạt động mua hàng mà không làm mất lịch sử đơn.</p>
        </div>
        <div class="customers-date"><i class="fa-regular fa-calendar"></i>{{ now()->format('d/m/Y') }}</div>
    </header>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <section class="customers-panel">
        <div class="customers-panel-head d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="customers-panel-title">Danh sách tài khoản khách hàng</div>
                <div class="customers-panel-subtitle">Quản lý thông tin và bảo mật tài khoản</div>
            </div>
            <form action="{{ route('admin.customers.index') }}" method="GET" class="customers-search d-flex gap-2">
                <div class="input-group input-group-sm">
                    <input type="search" name="keyword" value="{{ $keyword }}" class="form-control" placeholder="Tìm tên hoặc email..." aria-label="Tìm khách hàng">
                    <button class="btn btn-outline-secondary" type="submit" title="Tìm kiếm"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
                @if($keyword !== '')<a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-light border" title="Xóa tìm kiếm"><i class="fa-solid fa-xmark"></i></a>@endif
            </form>
        </div>

        @if($customers->isEmpty())
            <div class="customers-empty text-center text-muted"><i class="fa-solid fa-user-slash d-block mb-3"></i><strong>Không tìm thấy khách hàng</strong><div class="small mt-1">Hãy thử lại với từ khóa khác.</div></div>
        @else
            <div class="table-responsive">
                <table class="table customers-table mb-0">
                    <thead><tr><th>Khách hàng</th><th>Hoạt động</th><th>Cập nhật tài khoản</th></tr></thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>
                                    <div class="customer-identity">
                                        <span class="customer-avatar">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                                        <div><div class="customer-name">{{ $customer->name }}</div><div class="customer-email">{{ $customer->email }}</div></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="customer-activity">
                                        <span class="customer-order-count"><i class="fa-solid fa-bag-shopping"></i>{{ $customer->orders_count }} đơn hàng</span>
                                        <div class="customer-verified {{ $customer->email_verified_at ? 'is-verified' : 'is-pending' }}"><i class="fa-solid {{ $customer->email_verified_at ? 'fa-circle-check' : 'fa-clock' }}"></i>{{ $customer->email_verified_at ? 'Đã xác thực email' : 'Chưa xác thực email' }}</div>
                                    </div>
                                </td>
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
