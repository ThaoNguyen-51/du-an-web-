@extends('layouts.shop')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 text-center">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden p-4 bg-white">
                <div class="mb-3">
                    <div class="bg-danger-subtle text-hc rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-envelope-circle-check display-5"></i>
                    </div>
                </div>

                <h4 class="fw-bold text-dark mb-2">Xác Thực Địa Chỉ Email</h4>
                <p class="text-muted small mb-4">
                    Cảm ơn bạn đã đăng ký! Vui lòng kiểm tra hộp thư email để kích hoạt tài khoản trước khi bắt đầu mua sắm.
                </p>

                @if (session('message'))
                    <div class="alert alert-success border-0 shadow-sm small mb-3">
                        <i class="fa-solid fa-paper-plane me-1"></i>{{ session('message') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-hc w-100 py-2 fw-bold rounded-3 shadow-sm">
                        <i class="fa-solid fa-rotate-right me-1"></i>Gửi lại email xác thực
                    </button>
                </form>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted text-decoration-none small">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Đăng xuất tài khoản
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Kiểm tra tự động trạng thái xác thực
    let interval = setInterval(function() {
        fetch('/check-email-verified')
            .then(response => response.json())
            .then(data => {
                if (data.verified) {
                    clearInterval(interval);
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('logout') }}";

                    let csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = "{{ csrf_token() }}";

                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
    }, 2000);
</script>
@endsection