@extends('layouts.shop')

@section('content')
<style>
    .auth-page { min-height: calc(100vh - 220px); display: flex; align-items: center; }
    .auth-page > .row { width: 100%; flex: 1 1 100%; }
    .auth-page .auth-card { width: 100%; }
    .auth-card { border: 1px solid #edf0f5; box-shadow: 0 18px 45px rgba(15, 23, 42, .10); }
    .auth-card .card-header { background: linear-gradient(135deg, #d71921, #a90f16); }
    .auth-card .form-control, .auth-card .input-group-text { background: #f8fafc; border-color: #e5e7eb; }
    .auth-card .form-control:focus { background: #fff; border-color: #d71921; box-shadow: 0 0 0 .2rem rgba(215, 25, 33, .12); }
    .auth-password-toggle { border-left: 0; color: #667085; cursor: pointer; }
</style>
<div class="container py-4 auth-page">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card rounded-4 overflow-hidden">
                <div class="card-header text-white text-center py-4 border-0">
                    <div class="mb-2"><i class="fa-solid fa-shield-halved fs-2"></i></div>
                    <h4 class="fw-bold mb-1">Đặt lại mật khẩu</h4>
                    <p class="small text-white-50 mb-0">Tạo mật khẩu mới cho tài khoản của bạn</p>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())<div class="alert alert-danger border-0 small">{{ $errors->first() }}</div>@endif
                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="mb-3"><label class="form-label small fw-bold text-secondary">Địa chỉ Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required></div>
                        <div class="mb-3"><label class="form-label small fw-bold text-secondary">Mật khẩu mới</label><div class="input-group"><input type="password" name="password" id="reset-password" class="form-control border-end-0" minlength="8" required><button type="button" class="input-group-text auth-password-toggle" data-toggle-password="reset-password" aria-label="Hiện mật khẩu"><i class="fa-regular fa-eye"></i></button></div></div>
                        <div class="mb-4"><label class="form-label small fw-bold text-secondary">Nhập lại mật khẩu mới</label><div class="input-group"><input type="password" name="password_confirmation" id="reset-password-confirmation" class="form-control border-end-0" minlength="8" required><button type="button" class="input-group-text auth-password-toggle" data-toggle-password="reset-password-confirmation" aria-label="Hiện mật khẩu"><i class="fa-regular fa-eye"></i></button></div></div>
                        <button class="btn btn-hc w-100 py-2 fw-bold rounded-3">Lưu mật khẩu mới</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.togglePassword);
            const icon = button.querySelector('i');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isHidden);
            icon.classList.toggle('fa-eye-slash', isHidden);
            button.setAttribute('aria-label', isHidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    });
</script>
@endsection
