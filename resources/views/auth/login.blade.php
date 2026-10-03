@extends('layouts.shop')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
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
                                <input type="password" name="password" class="form-control border-start-0 bg-light" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-hc w-100 py-2.5 fw-bold text-uppercase shadow-sm rounded-3">
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
@endsection