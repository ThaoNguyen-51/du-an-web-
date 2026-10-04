@extends('layouts.shop')

@section('content')
<style>
    .bg-hc { background-color: #d71921 !important; }
    .text-hc { color: #d71921 !important; }
    .btn-hc { background-color: #d71921; color: #fff; border: none; }
    .btn-hc:hover { background-color: #b51219; color: #fff; }
    .form-check-input:checked { background-color: #d71921; border-color: #d71921; }
    .cursor-pointer { cursor: pointer; }
</style>

<div class="container my-4">
    <h3 class="fw-bold mb-4"><i class="fa-solid fa-cart-shopping me-2 text-hc"></i>Giỏ Hàng Của Bạn</h3>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(empty($cart))
        <div class="text-center py-5 bg-white rounded shadow-sm border">
            <i class="fa-solid fa-cart-flatbed-suitcases display-1 text-muted mb-3"></i>
            <p class="fs-5 text-muted fw-bold">Giỏ hàng của bạn đang trống!</p>
            <a href="{{ route('shop.index') }}" class="btn btn-hc btn-lg px-4 fs-6 fw-bold mt-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Tiếp tục mua sắm
            </a>
        </div>
    @else
        <form action="{{ route('user.orders.store') }}" method="POST" id="main-checkout-form">
            @csrf
            <input type="hidden" name="shipping_fee" id="input_shipping_fee" value="0">
            <input type="hidden" name="address_id" id="selected-address-id" value="">

            <div class="row g-4">
                <!-- Cột Trái: Sản phẩm -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">
                                            <input type="checkbox" class="form-check-input" id="check-all" checked title="Chọn tất cả">
                                        </th>
                                        <th>Sản phẩm</th>
                                        <th class="text-end">Đơn giá</th>
                                        <th class="text-center" style="width: 140px;">Số lượng</th>
                                        <th class="text-end">Thành tiền</th>
                                        <th class="text-center" style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cart as $key => $item)
                                        @php 
                                            $unitPrice = $item['price'] ?? 0;
                                            $qty = $item['quantity'] ?? 1;
                                            $subtotal = $unitPrice * $qty;
                                        @endphp
                                        <tr class="cart-item-row" data-key="{{ $key }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="selected_items[]" value="{{ $key }}" 
                                                       class="form-check-input item-checkbox" @checked(!session('buy_now_key') || session('buy_now_key') === $key)
                                                       data-unit-price="{{ $unitPrice }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    @if(!empty($item['image']))
                                                        <img src="{{ request()->getBaseUrl() . '/storage/' . $item['image'] }}" class="rounded border p-1" style="width: 60px; height: 60px; object-fit: contain;">
                                                    @else
                                                        <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">No image</div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold text-dark mb-1">{{ $item['name'] }}</div>
                                                        <span class="badge bg-light text-danger border">Công suất: {{ $item['capacity'] ?? 'Mặc định' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end text-hc fw-bold">
                                                {{ number_format($unitPrice, 0, ',', '.') }} đ
                                            </td>
                                            <td class="text-center">
                                                <input type="number" value="{{ $qty }}" min="1" 
                                                       class="form-control form-control-sm text-center mx-auto quantity-input" 
                                                       style="width: 65px;" data-key="{{ $key }}">
                                            </td>
                                            <td class="text-end text-hc fw-bold item-subtotal-display">
                                                {{ number_format($subtotal, 0, ',', '.') }} đ
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-remove-item" data-key="{{ $key }}">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <a href="{{ route('shop.index') }}" class="text-decoration-none text-hc fw-bold small">
                                <i class="fa-solid fa-arrow-left me-1"></i>Chọn thêm sản phẩm khác
                            </a>
                            <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0" id="btn-clear-cart">
                                <i class="fa-solid fa-broom me-1"></i>Xóa sạch giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cột Phải: Thông tin giao hàng & Thanh toán -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-3 sticky-top" style="top: 80px;">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-truck-fast me-2 text-hc"></i>Thông Tin Giao Hàng
                        </h5>

                        <div class="mb-3 p-2 rounded border border-danger-subtle bg-danger-subtle">
                            <label for="saved-address-select" class="form-label small fw-bold mb-1">
                                <i class="fa-solid fa-bookmark me-1 text-hc"></i>Chọn địa chỉ đã lưu
                            </label>
                            <select id="saved-address-select" class="form-select form-select-sm">
                                <option value="">-- Chọn địa chỉ giao hàng --</option>
                                @foreach($savedAddresses as $savedAddress)
                                    <option value="{{ $savedAddress->id }}"
                                        data-recipient-name="{{ $savedAddress->recipient_name }}"
                                        data-phone="{{ $savedAddress->phone }}"
                                        data-detail-address="{{ $savedAddress->address }}"
                                        data-province-id="{{ $savedAddress->province_id }}"
                                        data-province="{{ $savedAddress->province }}"
                                        data-district-id="{{ $savedAddress->district_id }}"
                                        data-district="{{ $savedAddress->district }}"
                                        data-ward-code="{{ $savedAddress->ward_code }}"
                                        data-ward="{{ $savedAddress->ward }}">
                                        {{ $savedAddress->label }} - {{ $savedAddress->recipient_name }} - {{ $savedAddress->phone }}
                                        {{ $savedAddress->is_default ? '(Mặc định)' : '' }}
                                    </option>
                                @endforeach
                                <option value="new">Nhập địa chỉ khác</option>
                            </select>
                            <div class="form-text">Địa chỉ được lấy từ sổ địa chỉ GHN của bạn.</div>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Họ và tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control form-control-sm" value="{{ auth()->user()->name ?? '' }}" required placeholder="Nhập tên người nhận">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                            <input type="tel" name="customer_phone" class="form-control form-control-sm" required placeholder="VD: 0987654321">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                            <select name="to_province_id" id="province_select" class="form-select form-select-sm" required>
                                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Quận / Huyện <span class="text-danger">*</span></label>
                            <select name="to_district_id" id="district_select" class="form-select form-select-sm" required disabled>
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Phường / Xã <span class="text-danger">*</span></label>
                            <select name="to_ward_code" id="ward_select" class="form-select form-select-sm" required disabled>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                            <input type="text" name="customer_address" class="form-control form-control-sm" required placeholder="Số nhà, tên đường...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ghi chú giao hàng</label>
                            <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="VD: Giao vào giờ hành chính..."></textarea>
                        </div>

                        <div class="mb-3 p-3 border rounded bg-white">
                            <label for="coupon-code" class="form-label small fw-bold"><i class="fa-solid fa-ticket text-hc me-1"></i>Mã giảm giá</label>
                            <div class="input-group input-group-sm">
                                <input id="coupon-code" name="coupon_code" type="text" class="form-control text-uppercase" maxlength="40" placeholder="Nhập mã giảm giá">
                                <button id="apply-coupon" class="btn btn-outline-danger" type="button">Áp dụng</button>
                            </div>
                            <div id="coupon-message" class="small mt-2" role="status" aria-live="polite"></div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold form-label small">Phương thức thanh toán <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2">
                                <label class="border p-2 rounded d-flex align-items-center gap-2 cursor-pointer">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <div>
                                        <strong class="d-block small">Thanh toán khi nhận hàng (COD)</strong>
                                        <small class="text-muted" style="font-size: 11px;">Trả tiền mặt khi nhận hàng</small>
                                    </div>
                                </label>

                                <label class="border p-2 rounded d-flex align-items-center gap-2 cursor-pointer">
                                    <input type="radio" name="payment_method" value="visa">
                                    <div><strong class="d-block small"><i class="fa-brands fa-cc-visa text-primary me-1"></i>Thẻ Visa</strong><small class="text-muted" style="font-size: 11px;">Thanh toán thẻ quốc tế qua cổng MoMo</small></div>
                                </label>

                                <label class="border p-2 rounded d-flex align-items-center gap-2 cursor-pointer">
                                    <input type="radio" name="payment_method" value="domestic">
                                    <div><strong class="d-block small"><i class="fa-solid fa-building-columns text-primary me-1"></i>Thẻ nội địa</strong><small class="text-muted" style="font-size: 11px;">Thanh toán ATM nội địa qua cổng MoMo</small></div>
                                </label>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded mb-3">
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Tạm tính:</span>
                                <span id="subtotal-display">0 đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Phí vận chuyển:</span>
                                <span class="text-danger fw-bold" id="shipping-fee-display">0 đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small text-success" id="discount-row" hidden>
                                <span>Mã giảm giá:</span>
                                <span class="fw-bold" id="discount-display">-0 đ</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Tổng thanh toán:</span>
                                <span class="fs-4 fw-bold text-hc" id="total-display">0 đ</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-hc w-100 py-3 fw-bold text-uppercase shadow-sm" id="btn-submit">
                            <i class="fa-solid fa-check-circle me-1"></i>Xác nhận đặt hàng
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <form id="action-form" method="POST" class="d-none">
            @csrf
            <input type="hidden" name="key" id="action-key">
            <input type="hidden" name="quantity" id="action-quantity">
        </form>
    @endif
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const appBaseUrl = @json(request()->getBaseUrl());
    const checkAll = document.getElementById("check-all");
    const itemCheckboxes = document.querySelectorAll(".item-checkbox");
    const subtotalDisplay = document.getElementById("subtotal-display");
    const shippingFeeDisplay = document.getElementById("shipping-fee-display");
    const totalDisplay = document.getElementById("total-display");
    const submitBtn = document.getElementById("btn-submit");
    const inputShippingFee = document.getElementById("input_shipping_fee");
    const couponInput = document.getElementById("coupon-code");
    const couponButton = document.getElementById("apply-coupon");
    const couponMessage = document.getElementById("coupon-message");
    const discountRow = document.getElementById("discount-row");
    const discountDisplay = document.getElementById("discount-display");

    const provinceSelect = document.getElementById("province_select");
    const districtSelect = document.getElementById("district_select");
    const wardSelect = document.getElementById("ward_select");

    let currentSubtotal = 0;
    let currentShippingFee = 0;
    let currentDiscount = 0;
    let appliedCouponCode = "";

    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + " đ";
    }

    function clearAppliedCoupon(message = "", clearCode = false) {
        currentDiscount = 0;
        appliedCouponCode = "";
        if (clearCode && couponInput) couponInput.value = "";
        if (discountRow) discountRow.hidden = true;
        if (couponMessage) {
            couponMessage.className = "small mt-2 text-muted";
            couponMessage.innerText = message;
        }
        calculateTotal();
    }

    // Hàm tính toán tổng tiền tạm tính theo trạng thái checkbox + số lượng hiện tại
    function calculateTotal() {
        currentSubtotal = 0;
        let count = 0;

        itemCheckboxes.forEach(cb => {
            if (cb.checked) {
                const row = cb.closest('tr');
                const unitPrice = parseFloat(cb.getAttribute("data-unit-price")) || 0;
                const qtyInput = row.querySelector('.quantity-input');
                const qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;
                
                currentSubtotal += (unitPrice * qty);
                count++;
            }
        });

        currentDiscount = Math.min(currentDiscount, currentSubtotal);
        const totalAmount = Math.max(0, currentSubtotal - currentDiscount) + currentShippingFee;

        if (inputShippingFee) inputShippingFee.value = currentShippingFee;

        if (subtotalDisplay) subtotalDisplay.innerText = new Intl.NumberFormat('vi-VN').format(currentSubtotal) + " đ";
        if (shippingFeeDisplay) shippingFeeDisplay.innerText = new Intl.NumberFormat('vi-VN').format(currentShippingFee) + " đ";
        if (totalDisplay) totalDisplay.innerText = formatMoney(totalAmount);
        if (discountDisplay) discountDisplay.innerText = "-" + formatMoney(currentDiscount);

        if (count === 0) {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<i class="fa-solid fa-exclamation-circle me-1"></i>Vui lòng chọn sản phẩm`;
            }
        } else {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `<i class="fa-solid fa-check-circle me-1"></i>Xác nhận đặt hàng (${count})`;
            }
        }
    }

    if (couponButton) {
        couponButton.addEventListener('click', async function () {
            const code = couponInput.value.trim().toUpperCase();
            const selectedItems = [...itemCheckboxes].filter(item => item.checked).map(item => item.value);

            if (!code) {
                clearAppliedCoupon('Vui lòng nhập mã giảm giá.');
                return;
            }
            if (selectedItems.length === 0) {
                clearAppliedCoupon('Vui lòng chọn sản phẩm trước khi áp mã.');
                return;
            }

            couponButton.disabled = true;
            couponButton.innerText = 'Đang kiểm tra...';
            const payload = new FormData();
            payload.append('coupon_code', code);
            selectedItems.forEach(item => payload.append('selected_items[]', item));

            try {
                const response = await fetch(@json(route('user.coupons.validate')), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: payload
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.errors?.coupon_code?.[0] || result.message || 'Không thể áp dụng mã.');

                currentDiscount = Number(result.discount) || 0;
                appliedCouponCode = result.code;
                couponInput.value = result.code;
                discountRow.hidden = false;
                couponMessage.className = 'small mt-2 text-success';
                couponMessage.innerText = `${result.message} Giảm ${formatMoney(currentDiscount)}.`;
                calculateTotal();
            } catch (error) {
                clearAppliedCoupon(error.message);
            } finally {
                couponButton.disabled = false;
                couponButton.innerText = 'Áp dụng';
            }
        });
    }

    if (couponInput) {
        couponInput.addEventListener('input', function () {
            if (appliedCouponCode && this.value.trim().toUpperCase() !== appliedCouponCode) {
                clearAppliedCoupon('Mã đã thay đổi. Vui lòng áp dụng lại.');
            }
        });
    }

    // GHN is used only for public address lookup. No order/customer payload is sent.
    function fetchShippingFee() {
        if (!districtSelect.value || !wardSelect.value) {
            currentShippingFee = 0;
            calculateTotal();
            return;
        }

        shippingFeeDisplay.innerText = "Đang tính...";
        fetch(`${appBaseUrl}/ghn/shipping-fee`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                to_district_id: districtSelect.value,
                to_ward_code: wardSelect.value
            })
        })
        .then(res => res.json())
        .then(res => {
            currentShippingFee = res.data && typeof res.data.total !== 'undefined'
                ? Number(res.data.total)
                : (typeof res.total !== 'undefined' ? Number(res.total) : 0);
            calculateTotal();
        })
        .catch(() => {
            currentShippingFee = 0;
            calculateTotal();
        });
    }

    if (provinceSelect) {
        fetch(`${appBaseUrl}/ghn/provinces`)
            .then(res => res.json())
            .then(res => {
                const provinces = Array.isArray(res.data) ? res.data : [];
                provinces.forEach(item => {
                    provinceSelect.innerHTML += `<option value="${item.ProvinceID}">${item.ProvinceName}</option>`;
                });
            });

        provinceSelect.addEventListener('change', function () {
            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            districtSelect.disabled = true;
            wardSelect.disabled = true;
            if (!this.value) return;

            fetch(`${appBaseUrl}/ghn/districts/${this.value}`)
                .then(res => res.json())
                .then(res => {
                    const districts = Array.isArray(res.data) ? res.data : [];
                    districts.forEach(item => {
                        districtSelect.innerHTML += `<option value="${item.DistrictID}">${item.DistrictName}</option>`;
                    });
                    districtSelect.disabled = false;
                    fetchShippingFee();
                });
        });

        districtSelect.addEventListener('change', function () {
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wardSelect.disabled = true;
            if (!this.value) return;

            fetch(`${appBaseUrl}/ghn/wards/${this.value}`)
                .then(res => res.json())
                .then(res => {
                    const wards = Array.isArray(res.data) ? res.data : [];
                    wards.forEach(item => {
                        wardSelect.innerHTML += `<option value="${item.WardCode}">${item.WardName}</option>`;
                    });
                    wardSelect.disabled = false;
                    fetchShippingFee();
                });
        });

        wardSelect.addEventListener('change', fetchShippingFee);
    }

    const savedAddressSelect = document.getElementById('saved-address-select');
    if (savedAddressSelect) {
        const addressData = option => option ? ({
            recipient_name: option.dataset.recipientName || '',
            phone: option.dataset.phone || '',
            address: option.dataset.detailAddress || '',
            province_id: option.dataset.provinceId || '',
            province: option.dataset.province || '',
            district_id: option.dataset.districtId || '',
            district: option.dataset.district || '',
            ward_code: option.dataset.wardCode || '',
            ward: option.dataset.ward || ''
        }) : null;
        const setSelectValue = (select, value) => {
            select.value = String(value ?? '');
        };
        const setSelectValueOrText = (select, value, text) => {
            setSelectValue(select, value);
            if (select.value || !text) return;
            const normalized = text.trim().toLowerCase();
            const option = Array.from(select.options).find(item => item.textContent.trim().toLowerCase() === normalized);
            if (option) select.value = option.value;
        };
        const loadDistrictsForAddress = (data, provinceId) => fetch(`${appBaseUrl}/ghn/districts/${provinceId}`)
            .then(response => response.json())
            .then(result => {
                districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                (Array.isArray(result.data) ? result.data : []).forEach(item => {
                    districtSelect.innerHTML += `<option value="${item.DistrictID}">${item.DistrictName}</option>`;
                });
                districtSelect.disabled = false;
                setSelectValueOrText(districtSelect, data.district_id, data.district);
                return fetch(`${appBaseUrl}/ghn/wards/${districtSelect.value}`);
            });
        const loadWardsForAddress = (data, provinceId) => loadDistrictsForAddress(data, provinceId)
            .then(response => response.json())
            .then(result => {
                wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
                (Array.isArray(result.data) ? result.data : []).forEach(item => {
                    wardSelect.innerHTML += `<option value="${item.WardCode}">${item.WardName}</option>`;
                });
                wardSelect.disabled = false;
                setSelectValueOrText(wardSelect, data.ward_code, data.ward);
                fetchShippingFee();
            });
        const applySavedAddress = (data) => fetch(`${appBaseUrl}/ghn/provinces`)
            .then(response => response.json())
            .then(result => {
                if (provinceSelect.options.length <= 1) {
                    (Array.isArray(result.data) ? result.data : []).forEach(item => {
                        provinceSelect.innerHTML += `<option value="${item.ProvinceID}">${item.ProvinceName}</option>`;
                    });
                }
                setSelectValueOrText(provinceSelect, data.province_id, data.province);
                if (!provinceSelect.value) throw new Error('Không xác định được tỉnh/thành phố GHN.');
                return loadWardsForAddress(data, provinceSelect.value);
            });

        savedAddressSelect.addEventListener('change', function () {
            if (this.value === 'new' || !this.value) {
                document.getElementById('selected-address-id').value = '';
                document.querySelector('[name="customer_name"]').value = '';
                document.querySelector('[name="customer_phone"]').value = '';
                document.querySelector('[name="customer_address"]').value = '';
                provinceSelect.value = '';
                districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
                wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
                districtSelect.disabled = true;
                wardSelect.disabled = true;
                fetchShippingFee();
                return;
            }

            const data = addressData(this.options[this.selectedIndex]);
            document.getElementById('selected-address-id').value = this.value;
            document.querySelector('[name="customer_name"]').value = data.recipient_name || '';
            document.querySelector('[name="customer_phone"]').value = data.phone || '';
            document.querySelector('[name="customer_address"]').value = data.address || '';
            districtSelect.disabled = true;
            wardSelect.disabled = true;
            applySavedAddress(data).catch(() => {
                fetchShippingFee();
            });
        });

        const defaultAddress = Array.from(savedAddressSelect.options).find(option => option.dataset.provinceId && option.textContent.includes('(Mặc định)'));
        if (defaultAddress) {
            savedAddressSelect.value = defaultAddress.value;
            savedAddressSelect.dispatchEvent(new Event('change'));
        }
    }

    if (checkAll) {
        checkAll.addEventListener("change", function () {
            itemCheckboxes.forEach(cb => cb.checked = this.checked);
            if (appliedCouponCode) clearAppliedCoupon('Danh sách sản phẩm đã đổi. Vui lòng áp dụng lại mã.', true);
            calculateTotal();
        });
    }

    itemCheckboxes.forEach(cb => {
        cb.addEventListener("change", function () {
            if (!this.checked && checkAll) checkAll.checked = false;
            if (appliedCouponCode) clearAppliedCoupon('Danh sách sản phẩm đã đổi. Vui lòng áp dụng lại mã.', true);
            calculateTotal();
        });
    });

    document.querySelectorAll(".quantity-input").forEach(input => {
        input.addEventListener("change", function () {
            const form = document.getElementById("action-form");
            form.action = "{{ route('cart.update') }}";
            document.getElementById("action-key").value = this.getAttribute("data-key");
            document.getElementById("action-quantity").value = this.value;
            form.submit();
        });
    });

    document.querySelectorAll(".btn-remove-item").forEach(btn => {
        btn.addEventListener("click", function () {
            if (confirm("Bạn có chắc muốn xóa sản phẩm này?")) {
                const form = document.getElementById("action-form");
                form.action = "{{ route('cart.remove') }}";
                document.getElementById("action-key").value = this.getAttribute("data-key");
                form.submit();
            }
        });
    });

    const btnClearCart = document.getElementById("btn-clear-cart");
    if (btnClearCart) {
        btnClearCart.addEventListener("click", function () {
            if (confirm("Bạn có chắc muốn xóa toàn bộ giỏ hàng?")) {
                const form = document.getElementById("action-form");
                form.action = "{{ route('cart.clear') }}";
                form.submit();
            }
        });
    }

    calculateTotal();
});
</script>
@endsection