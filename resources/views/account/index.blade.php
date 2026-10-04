@extends('layouts.shop')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="small text-danger fw-bold text-uppercase">Tài khoản của tôi</div>
            <h1 class="h3 fw-bold mb-0">Xin chào, {{ $user->name }}</h1>
        </div>
        <a href="{{ route('shop.index') }}" class="btn btn-outline-danger"><i class="fa-solid fa-store me-2"></i>Tiếp tục mua sắm</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3"><i class="fa-solid fa-user me-2 text-danger"></i>Thông tin cá nhân</div>
                <div class="card-body">
                    <form action="{{ route('account.profile.update') }}" method="POST" class="row g-3">
                        @csrf @method('PUT')
                        <div class="col-md-6"><label class="form-label">Họ tên</label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
                        <div class="col-12"><button class="btn btn-hc">Lưu thông tin</button></div>
                    </form>
                </div>
            </section>

            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3"><i class="fa-solid fa-lock me-2 text-danger"></i>Đổi mật khẩu</div>
                <div class="card-body">
                    <form action="{{ route('account.password.update') }}" method="POST" class="row g-3">
                        @csrf @method('PUT')
                        <div class="col-12"><label class="form-label">Mật khẩu hiện tại</label><input type="password" name="current_password" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Mật khẩu mới</label><input type="password" name="password" class="form-control" minlength="8" required></div>
                        <div class="col-md-6"><label class="form-label">Nhập lại mật khẩu mới</label><input type="password" name="password_confirmation" class="form-control" minlength="8" required></div>
                        <div class="col-12"><button class="btn btn-outline-danger">Đổi mật khẩu</button></div>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3"><strong><i class="fa-solid fa-location-dot me-2 text-danger"></i>Sổ địa chỉ giao hàng</strong><button class="btn btn-sm btn-danger" data-bs-toggle="collapse" data-bs-target="#new-address">Thêm địa chỉ</button></div>
                <div class="card-body">
                    <form action="{{ route('account.addresses.store') }}" method="POST" class="collapse row g-2 mb-3" id="new-address">
                        @csrf
                        <div class="col-6"><input name="label" class="form-control form-control-sm" placeholder="Nhãn: Nhà riêng" required></div>
                        <div class="col-6"><input name="recipient_name" class="form-control form-control-sm" placeholder="Người nhận" required></div>
                        <div class="col-6"><input name="phone" class="form-control form-control-sm" placeholder="Số điện thoại" required></div>
                        <div class="col-6">
                            <select name="province_id" id="account-province" class="form-select form-select-sm" required>
                                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            </select>
                            <input type="hidden" name="province" id="account-province-name">
                        </div>
                        <div class="col-6">
                            <select name="district_id" id="account-district" class="form-select form-select-sm" required disabled>
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                            <input type="hidden" name="district" id="account-district-name">
                        </div>
                        <div class="col-6">
                            <select name="ward_code" id="account-ward" class="form-select form-select-sm" required disabled>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                            <input type="hidden" name="ward" id="account-ward-name">
                        </div>
                        <div class="col-12"><textarea name="address" class="form-control form-control-sm" placeholder="Số nhà, tên đường..." required></textarea></div>
                        <div class="col-12"><label class="small"><input type="checkbox" name="is_default" value="1"> Đặt làm địa chỉ mặc định</label></div>
                        <div class="col-12"><button class="btn btn-sm btn-hc">Lưu địa chỉ</button></div>
                    </form>
                    @forelse($addresses as $address)
                        <div class="border rounded p-3 mb-2 {{ $address->is_default ? 'border-danger' : '' }}">
                            <div class="d-flex justify-content-between gap-2"><strong>{{ $address->label }}</strong>@if($address->is_default)<span class="badge text-bg-danger">Mặc định</span>@endif</div>
                            <div class="small mt-2">{{ $address->recipient_name }} · {{ $address->phone }}</div>
                            <div class="small text-muted">{{ collect([$address->address, $address->ward, $address->district, $address->province])->filter()->implode(', ') }}</div>
                            <div class="d-flex gap-2 mt-2">
                                @unless($address->is_default)<form action="{{ route('account.addresses.default', $address) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-secondary">Đặt mặc định</button></form>@endunless
                                <form action="{{ route('account.addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Xóa địa chỉ này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Bạn chưa lưu địa chỉ giao hàng.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3"><strong><i class="fa-solid fa-clock-rotate-left me-2 text-danger"></i>Lịch sử mua hàng</strong><a href="{{ route('user.orders.index') }}" class="btn btn-sm btn-outline-danger">Xem tất cả</a></div>
        <div class="card-body p-0">
            @forelse($orders as $order)
                <a href="{{ route('user.orders.show', $order) }}" class="d-flex justify-content-between align-items-center gap-3 text-decoration-none text-dark border-bottom p-3">
                    <span><strong>Đơn #{{ $order->id }}</strong><small class="d-block text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small></span>
                    <span class="text-end"><strong class="text-danger">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong><small class="d-block text-muted">{{ $order->status_label }}</small></span>
                </a>
            @empty <p class="p-3 text-muted mb-0">Bạn chưa có đơn hàng.</p> @endforelse
        </div>
    </section>
</div>
<script>
    (function () {
        const province = document.getElementById('account-province');
        const district = document.getElementById('account-district');
        const ward = document.getElementById('account-ward');
        const provinceName = document.getElementById('account-province-name');
        const districtName = document.getElementById('account-district-name');
        const wardName = document.getElementById('account-ward-name');
        if (!province) return;
        const base = @json(url('/'));
        const reset = (select, placeholder) => {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = true;
        };
        const load = (url, select, valueKey, labelKey) => fetch(url).then(response => response.json()).then(result => {
            (Array.isArray(result.data) ? result.data : []).forEach(item => {
                const option = document.createElement('option');
                option.value = item[valueKey];
                option.textContent = item[labelKey];
                select.appendChild(option);
            });
            select.disabled = false;
        });
        load(`${base}/ghn/provinces`, province, 'ProvinceID', 'ProvinceName');
        province.addEventListener('change', function () {
            provinceName.value = this.options[this.selectedIndex]?.text || '';
            reset(district, '-- Chọn Quận/Huyện --');
            reset(ward, '-- Chọn Phường/Xã --');
            districtName.value = '';
            wardName.value = '';
            if (this.value) load(`${base}/ghn/districts/${this.value}`, district, 'DistrictID', 'DistrictName');
        });
        district.addEventListener('change', function () {
            districtName.value = this.options[this.selectedIndex]?.text || '';
            reset(ward, '-- Chọn Phường/Xã --');
            wardName.value = '';
            if (this.value) load(`${base}/ghn/wards/${this.value}`, ward, 'WardCode', 'WardName');
        });
        ward.addEventListener('change', function () {
            wardName.value = this.options[this.selectedIndex]?.text || '';
        });
    })();
</script>
@endsection
