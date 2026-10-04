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
    .auth-password-toggle:hover { color: #d71921; }
    .auth-submit { min-height: 46px; }
</style>
<div class="container py-4 auth-page">
    <div class="row justify-content-center w-100">
        <div class="col-md-5 col-lg-4">
            <div class="card auth-card rounded-4 overflow-hidden">
                <div class="card-header bg-hc text-white text-center py-4 border-0">
                    <h4 class="fw-bold mb-1"><i class="fa-solid fa-right-to-bracket me-2"></i>Đăng Nhập</h4>
                    <p class="small text-white-50 mb-0">Hệ thống siêu thị điện máy HC</p>
                </div>
                <div class="card-body p-4 bg-white">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm small" role="alert">
                            <i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm small mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Địa chỉ Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 bg-light" value="{{ old('email') }}" placeholder="nhapemail@example.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" id="login-password" class="form-control border-start-0 border-end-0" placeholder="••••••••" required>
                                <button type="button" class="input-group-text auth-password-toggle" data-toggle-password="login-password" aria-label="Hiện mật khẩu"><i class="fa-regular fa-eye"></i></button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-3"><a href="{{ route('password.request') }}" class="small text-hc fw-semibold text-decoration-none">Quên mật khẩu?</a></div>
                        <button type="submit" class="btn btn-hc w-100 py-2 fw-bold text-uppercase shadow-sm rounded-3 auth-submit">
                            Đăng nhập ngay
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top small text-muted">
                        Chưa có tài khoản? 
                        <a href="{{ route('register') }}" class="text-hc fw-bold text-decoration-none ms-1">Đăng ký ngay</a>
                    </div>
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