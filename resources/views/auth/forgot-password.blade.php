@extends('layouts.shop')

@section('content')
<style>
    .auth-page { min-height: calc(100vh - 220px); display: flex; align-items: center; }
    .auth-page > .row { width: 100%; flex: 1 1 100%; }
    .auth-page .auth-card { width: 100%; }
    .auth-card { border: 1px solid #edf0f5; box-shadow: 0 18px 45px rgba(15, 23, 42, .10); }
    .auth-card .card-header { background: linear-gradient(135deg, #d71921, #a90f16); }
    .auth-card .form-control { background: #f8fafc; border-color: #e5e7eb; }
    .auth-card .form-control:focus { background: #fff; border-color: #d71921; box-shadow: 0 0 0 .2rem rgba(215, 25, 33, .12); }
</style>
<div class="container py-4 auth-page">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card rounded-4 overflow-hidden">
                <div class="card-header text-white text-center py-4 border-0">
                    <div class="mb-2"><i class="fa-solid fa-key fs-2"></i></div>
                    <h4 class="fw-bold mb-1">Quên mật khẩu?</h4>
                    <p class="small text-white-50 mb-0">Nhập email để nhận liên kết đặt lại mật khẩu</p>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))<div class="alert alert-success border-0 small">{{ session('success') }}</div>@endif
                    @if($errors->any())<div class="alert alert-danger border-0 small">{{ $errors->first() }}</div>@endif
                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <label class="form-label small fw-bold text-secondary">Địa chỉ Email</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control border-start-0" value="{{ old('email') }}" placeholder="nhapemail@example.com" required autofocus>
                        </div>
                        <button class="btn btn-hc w-100 py-2 fw-bold rounded-3">Gửi liên kết đặt lại mật khẩu</button>
                    </form>
                    <div class="text-center mt-4 pt-3 border-top small"><a href="{{ route('login') }}" class="text-hc fw-semibold text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Quay lại đăng nhập</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
