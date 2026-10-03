@extends('layouts.shop')

@section('content')
<style>
    .staff-page { --staff-ink: #172033; --staff-muted: #718096; --staff-line: #e5e9f0; color: var(--staff-ink); }
    .staff-heading { padding-bottom: 20px; border-bottom: 1px solid var(--staff-line); margin-bottom: 22px; }
    .staff-heading h1 { font-size: 1.45rem; font-weight: 800; margin: 0; }
    .staff-heading p { color: var(--staff-muted); margin: 6px 0 0; font-size: .88rem; }
    .staff-panel { background: #fff; border: 1px solid var(--staff-line); border-radius: 8px; }
    .staff-panel-title { padding: 15px 18px; border-bottom: 1px solid var(--staff-line); font-weight: 750; }
    .staff-form { padding: 18px; }
    .staff-table { min-width: 760px; }
    .staff-table th { background: #f8f9fb; color: var(--staff-muted); font-size: .72rem; text-transform: uppercase; white-space: nowrap; }
    .staff-table td, .staff-table th { vertical-align: middle; padding: 12px 14px; }
    .staff-edit-form { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 7px; align-items: center; }
    .staff-edit-form .form-control { min-width: 0; font-size: .8rem; }
    @media (max-width: 767.98px) { .staff-edit-form { grid-template-columns: 1fr; } }
</style>

<div class="staff-page">
    <header class="staff-heading">
        <h1>Quản lý nhân viên</h1>
        <p>Tạo và cập nhật tài khoản nhân viên. Nhân viên chỉ được quản lý sản phẩm và đơn hàng.</p>
    </header>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <section class="staff-panel mb-4">
        <div class="staff-panel-title"><i class="fa-solid fa-user-plus text-danger me-2"></i>Thêm nhân viên</div>
        <form action="{{ route('admin.staff.store') }}" method="POST" class="staff-form">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label" for="staff-name">Họ tên</label><input id="staff-name" name="name" value="{{ old('name') }}" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label" for="staff-email">Email</label><input id="staff-email" name="email" type="email" value="{{ old('email') }}" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label" for="staff-password">Mật khẩu</label><input id="staff-password" name="password" type="password" minlength="8" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label" for="staff-password-confirmation">Nhập lại mật khẩu</label><input id="staff-password-confirmation" name="password_confirmation" type="password" minlength="8" class="form-control" required></div>
                <div class="col-md-2 d-grid"><button class="btn btn-danger"><i class="fa-solid fa-plus me-1"></i>Tạo tài khoản</button></div>
            </div>
        </form>
    </section>

    <section class="staff-panel">
        <div class="staff-panel-title d-flex justify-content-between align-items-center"><span>Danh sách nhân viên</span><span class="badge bg-light text-dark border">{{ $staffMembers->total() }}</span></div>
        @if($staffMembers->isEmpty())
            <div class="text-center text-muted py-5">Chưa có tài khoản nhân viên.</div>
        @else
            <div class="table-responsive">
                <table class="table staff-table mb-0">
                    <thead><tr><th>Thông tin</th><th>Cập nhật tài khoản</th><th class="text-end">Thao tác</th></tr></thead>
                    <tbody>
                        @foreach($staffMembers as $staff)
                            <tr>
                                <td><strong class="d-block">{{ $staff->name }}</strong><small class="text-muted">Tạo ngày {{ $staff->created_at?->format('d/m/Y') }}</small></td>
                                <td>
                                    <form action="{{ route('admin.staff.update', $staff) }}" method="POST" class="staff-edit-form">
                                        @csrf
                                        @method('PUT')
                                        <input name="name" value="{{ $staff->name }}" class="form-control" aria-label="Họ tên nhân viên" required>
                                        <input name="email" type="email" value="{{ $staff->email }}" class="form-control" aria-label="Email nhân viên" required>
                                        <input name="password" type="password" minlength="8" class="form-control" aria-label="Mật khẩu mới (không bắt buộc)" placeholder="Mật khẩu mới">
                                        <input name="password_confirmation" type="password" minlength="8" class="form-control" aria-label="Xác nhận mật khẩu mới" placeholder="Xác nhận mật khẩu">
                                        <button class="btn btn-sm btn-outline-primary">Lưu</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('admin.staff.destroy', $staff) }}" method="POST" onsubmit="return confirm('Xóa tài khoản nhân viên này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Xóa nhân viên" aria-label="Xóa nhân viên"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($staffMembers->hasPages())<div class="p-3">{{ $staffMembers->links('pagination::bootstrap-5') }}</div>@endif
        @endif
    </section>
</div>
@endsection
