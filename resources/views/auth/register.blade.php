@extends('layouts.shop')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-hc text-white text-center py-4 border-0">
                    <h4 class="fw-bold mb-1"><i class="fa-solid fa-user-plus me-2"></i>Đăng Ký Tài Khoản</h4>
                    <p class="small text-white-50 mb-0">Trở thành thành viên để nhận ưu đãi từ HC</p>
                </div>
                <div class="card-body p-4 bg-white">

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm small mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('register.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Họ và tên <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 bg-light" required value="{{ old('name') }}" placeholder="Nguyễn Văn A">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Địa chỉ Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 bg-light" required value="{{ old('email') }}" placeholder="nhapemail@example.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 bg-light" required placeholder="Tối thiểu 6 ký tự">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-shield-halved"></i></span>
                                <input type="password" name="password_confirmation" class="form-control border-start-0 bg-light" required placeholder="Nhập lại mật khẩu">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-hc w-100 py-2.5 fw-bold text-uppercase shadow-sm rounded-3">
                            Đăng ký tài khoản
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top small text-muted">
                        Đã có tài khoản? 
                        <a href="{{ route('login') }}" class="text-hc fw-bold text-decoration-none ms-1">Đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection