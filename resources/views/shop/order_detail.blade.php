@extends('layouts.shop')

@section('content')
<style>
    .bg-hc { background-color: #d71921 !important; }
    .text-hc { color: #d71921 !important; }
    .btn-hc { background-color: #d71921; color: #fff; border: none; }
    .btn-hc:hover { background-color: #b51219; color: #fff; }
    .message-list { max-height: 340px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding-right: 6px; }
    .chat-message { display: flex; max-width: 82%; }
    .chat-message--customer { margin-left: auto; justify-content: flex-end; }
    .chat-message--admin { margin-right: auto; justify-content: flex-start; }
    .message { border-radius: 16px; padding: 11px 13px; font-size: .88rem; box-shadow: 0 8px 18px rgba(20, 33, 61, 0.05); word-break: break-word; }
    .message-admin { background: #f3f5f9; color: #172033; border: 1px solid #e7ebf2; }
    .message-customer { background: linear-gradient(135deg, #d71921 0%, #ef3d45 100%); color: #fff; }
    .message-meta { font-size: .72rem; opacity: .8; }
</style>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="fa-solid fa-file-invoice text-hc me-2"></i>Chi Tiết Đơn Hàng #{{ $order->id }}</h3>
        <a href="{{ route('user.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Thông tin giao hàng & Đơn hàng -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3 border-bottom">
                    <i class="fa-solid fa-truck-ramping me-2 text-hc"></i>Thông tin đơn hàng
                </div>
                <div class="card-body lh-lg">
                    <p class="mb-2"><strong>Mã đơn hàng:</strong> <span class="badge bg-secondary">#{{ $order->id }}</span></p>
                    <p class="mb-2"><strong>Ngày đặt:</strong> {{ $order->created_at ? $order->created_at->format('H:i d/m/Y') : 'N/A' }}</p>
                    <p class="mb-2"><strong>Người nhận:</strong> {{ $order->customer_name }}</p>
                    <p class="mb-2"><strong>Số điện thoại:</strong> {{ $order->customer_phone }}</p>
                    <p class="mb-2"><strong>Địa chỉ giao hàng:</strong> {{ $order->customer_address }}</p>

                    @if(!empty($order->note))
                        <p class="mb-2"><strong>Ghi chú:</strong> <em class="text-muted">{{ $order->note }}</em></p>
                    @endif

                    @php($paymentMethodLabels = ['cod' => 'Thanh toán khi nhận hàng (COD)', 'visa' => 'Visa', 'domestic' => 'Thẻ nội địa'])
                    <p class="mb-2">
                        <strong>Phương thức thanh toán:</strong> 
                        <span class="badge bg-light text-dark border">{{ $paymentMethodLabels[$order->payment_method ?? 'cod'] ?? strtoupper($order->payment_method ?? 'COD') }}</span>
                    </p>
                    @php($lastTransaction = $order->paymentTransactions->sortByDesc('id')->first())
                    @if($lastTransaction)
                        <p class="mb-2">
                            <strong>Trạng thái thanh toán:</strong>
                            <span class="badge {{ $lastTransaction->status === 'paid' ? 'bg-success' : ($lastTransaction->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ $lastTransaction->status === 'paid' ? 'Đã thanh toán' : ($lastTransaction->status === 'failed' ? 'Thất bại' : 'Đang chờ') }}
                            </span>
                        </p>
                    @endif

                    <p class="mb-2">
                        <strong>Trạng thái đơn:</strong>
                        @switch(\App\Models\Order::normalizeStatus($order->status))
                            @case('pending_confirmation')
                                <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                                @break
                            @case('awaiting_pickup')
                                <span class="badge bg-info text-dark">Chờ lấy hàng</span>
                                @break
                            @case('awaiting_delivery')
                                <span class="badge bg-primary">Chờ giao hàng</span>
                                @break
                            @case('in_transit')
                                <span class="badge bg-success">Đang giao</span>
                                @break
                            @case('completed')
                                <span class="badge bg-success">Đã hoàn thành</span>
                                @break
                            @case('cancelled')
                                <span class="badge bg-danger">Đã hủy</span>
                                @break
                            @default
                                <span class="badge bg-secondary">{{ $order->status_label ?? 'Không xác định' }}</span>
                        @endswitch
                    </p>

                    <div class="alert alert-light border mb-3">
                        <strong>Tin nhắn trạng thái:</strong><br>
                        {{ $order->status_message }}
                    </div>

                    <p class="mb-0">
                        <strong>Trạng thái vận chuyển:</strong> 
                        @switch($order->shipping_status)
                            @case('not_shipped')
                                    <span class="badge bg-secondary">Đang chuẩn bị giao hàng</span>
                                @break
                            @case('ready_to_pick')
                                <span class="badge bg-info text-dark">Chờ lấy hàng</span>
                                @break
                            @case('delivering')
                                <span class="badge bg-primary">Đang giao hàng</span>
                                @break
                            @case('delivered')
                                <span class="badge bg-success">Đang giao</span>
                                @break
                            @case('cancel')
                                <span class="badge bg-danger">Đã hủy giao</span>
                                @break
                            @default
                                <span class="badge bg-secondary">{{ $order->shipping_status ?? 'Chưa giao' }}</span>
                        @endswitch
                    </p>

                    @if(\App\Models\Order::isOnlinePaymentMethod($order->payment_method) && $lastTransaction?->status !== 'paid' && !$order->ghn_order_code)
                        <a href="{{ route('payment.momo.pay', $order) }}" class="btn btn-hc mt-3">
                            <i class="fa-solid fa-rotate-right me-1"></i>Thanh toán lại
                        </a>
                    @endif

                    @if($order->ghn_order_code || $order->shipping_code || $order->virtual_tracking_code)
                        <p class="mb-0 mt-2">
                            <span class="fw-bold text-hc">{{ $order->ghn_order_code ?? $order->shipping_code ?? $order->virtual_tracking_code }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white fw-bold py-3 border-bottom">
                    <i class="fa-solid fa-comments me-2 text-hc"></i>Nhắn tin với shop
                </div>
                <div class="card-body">
                    <div class="message-list mb-3" id="order-message-list" data-messages-url="{{ route('user.orders.messages', $order) }}">
                        @forelse($order->messages as $message)
                            <div class="chat-message message-entry {{ $message->sender_role === 'customer' ? 'chat-message--customer' : 'chat-message--admin' }}" data-message-id="{{ $message->id }}">
                                <div class="message {{ $message->sender_role === 'customer' ? 'message-customer' : 'message-admin' }}">
                                    <div class="small fw-semibold mb-1">
                                        {{ $message->sender_role === 'customer' ? 'Bạn' : 'Shop' }}
                                    </div>
                                    <div>{{ $message->message }}</div>
                                    <div class="message-meta mt-1 {{ $message->sender_role === 'customer' ? 'text-white-50' : 'text-muted' }}">
                                        {{ $message->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-3">Chưa có tin nhắn nào cho đơn hàng này.</div>
                        @endforelse
                    </div>

                    <form action="{{ route('user.orders.sendMessage', $order) }}" method="POST" id="order-message-form">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="message" class="form-control" placeholder="Nhập tin nhắn cho shop..." maxlength="1000" required autocomplete="off">
                            <button type="submit" class="btn btn-hc" id="order-message-submit">Gửi</button>
                        </div>
                        <div class="small text-danger mt-2" id="order-message-error" role="alert"></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Chi tiết sản phẩm trong đơn -->
        <div class="col-md-7" id="review-order-items">
            <div class="card border-0 shadow-sm d-flex flex-column justify-content-between">
                <div>
                    <div class="card-header bg-white fw-bold py-3 border-bottom">
                        <i class="fa-solid fa-box-open me-2 text-hc"></i>Sản phẩm đã đặt
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Đơn giá</th>
                                        <th class="text-center">Số lượng</th>
                                        <th class="text-end">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if(!empty($item->image))
                                                        <img src="{{ request()->getBaseUrl() . '/storage/' . $item->image }}" class="rounded border p-1" style="width: 45px; height: 45px; object-fit: contain;">
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                                        @if(!empty($item->capacity))
                                                            <small class="badge bg-light text-danger border">Công suất: {{ $item->capacity }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ number_format($item->price, 0, ',', '.') }} đ</td>
                                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                            <td class="text-end fw-bold text-hc">
                                                {{ number_format(($item->subtotal ?? ($item->price * $item->quantity)), 0, ',', '.') }} đ
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-white p-3 border-top">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tạm tính:</span>
                        <strong>{{ number_format($order->items->sum(fn ($item) => $item->subtotal ?? ($item->price * $item->quantity)), 0, ',', '.') }} đ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Phí vận chuyển:</span>
                        <strong class="text-danger">{{ number_format($order->shipping_fee ?? max(0, $order->total_amount - $order->items->sum(fn ($item) => $item->subtotal ?? ($item->price * $item->quantity))), 0, ',', '.') }} đ</strong>
                    </div>
                    @if((float) $order->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Mã {{ $order->coupon_code }}:</span>
                            <strong>-{{ number_format((float) $order->discount_amount, 0, ',', '.') }} đ</strong>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between text-hc fs-5 fw-bold border-top pt-2">
                        <span>Tổng tiền thanh toán:</span>
                        <span>{{ number_format($order->total_amount, 0, ',', '.') }} đ</span>
                    </div>
                </div>
            </div>

            @if(\App\Models\Order::normalizeStatus($order->status) === \App\Models\Order::STATUS_COMPLETED)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white fw-bold py-3 border-bottom">
                        <i class="fa-solid fa-star text-warning me-2"></i>Đánh giá sản phẩm
                    </div>
                    <div class="card-body">
                            <p class="text-muted small mb-3">Chia sẻ trải nghiệm của bạn về các sản phẩm trong đơn hàng.</p>
                            <div class="row g-3">
                                @foreach($order->items as $item)
                                    @if($item->airConditioner)
                                        <div class="col-12">
                                            <div class="border rounded p-3">
                                                <div class="fw-bold text-dark mb-2">{{ $item->product_name }}</div>
                                                @if($item->purchaseReview)
                                                    <div class="small">
                                                        <span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Đã đánh giá</span>
                                                        <span class="text-warning ms-1">{{ str_repeat('★', $item->purchaseReview->rating) }}</span>
                                                        <p class="mb-1 mt-2 text-break">{{ $item->purchaseReview->comment }}</p>
                                                        @if(!empty($item->purchaseReview->images))
                                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                                @foreach($item->purchaseReview->images as $reviewImage)
                                                                    @php($reviewImageUrl = filter_var($reviewImage, FILTER_VALIDATE_URL) ? $reviewImage : asset('storage/' . $reviewImage))
                                                                    <a href="{{ $reviewImageUrl }}" target="_blank" rel="noopener">
                                                                        <img src="{{ $reviewImageUrl }}" alt="Ảnh đánh giá" class="rounded border" style="width:58px;height:58px;object-fit:cover;">
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if($item->purchaseReview->admin_reply)
                                                            <div class="text-muted border-start border-2 border-danger ps-2 mt-2">
                                                                <strong class="text-danger">Shop phản hồi:</strong> {{ $item->purchaseReview->admin_reply }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <form action="{{ route('product-reviews.store', $item->airConditioner) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                        <div class="row g-2">
                                                            <div class="col-sm-4">
                                                                <select name="rating" class="form-select form-select-sm" aria-label="Số sao đánh giá" required>
                                                                    <option value="5">5 sao</option>
                                                                    <option value="4">4 sao</option>
                                                                    <option value="3">3 sao</option>
                                                                    <option value="2">2 sao</option>
                                                                    <option value="1">1 sao</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <textarea name="comment" class="form-control form-control-sm" rows="2" minlength="5" maxlength="2000" placeholder="Chia sẻ nhận xét của bạn..." required></textarea>
                                                            </div>
                                                            <div class="col-12">
                                                                <button type="submit" class="btn btn-sm btn-hc"><i class="fa-solid fa-paper-plane me-1"></i>Gửi đánh giá</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('order-message-list');
    const form = document.getElementById('order-message-form');
    const input = form?.querySelector('input[name="message"]');
    const submit = document.getElementById('order-message-submit');
    const error = document.getElementById('order-message-error');
    if (!list || !form || !input || !submit) return;

    let newestMessageId = Math.max(0, ...[...list.querySelectorAll('[data-message-id]')].map(el => Number(el.dataset.messageId) || 0));

    const appendMessages = (messages) => {
        let added = false;
        messages.forEach(message => {
            const id = Number(message.id);
            if (!id || id <= newestMessageId || list.querySelector(`[data-message-id="${id}"]`)) return;
            const customerMessage = message.sender_role === 'customer';
            const wrapper = document.createElement('div');
            wrapper.className = `chat-message message-entry ${customerMessage ? 'chat-message--customer' : 'chat-message--admin'}`;
            wrapper.dataset.messageId = id;
            wrapper.innerHTML = `
                <div class="message ${customerMessage ? 'message-customer' : 'message-admin'}">
                    <div class="small fw-semibold mb-1">${customerMessage ? 'Bạn' : 'Shop'}</div>
                    <div></div>
                    <div class="message-meta mt-1 ${customerMessage ? 'text-white-50' : 'text-muted'}">${message.created_at || ''}</div>
                </div>`;
            wrapper.querySelector('.message > div:nth-child(2)').textContent = message.message;
            list.querySelector('.text-center.text-muted')?.remove();
            list.appendChild(wrapper);
            newestMessageId = Math.max(newestMessageId, id);
            added = true;
        });
        if (added) list.scrollTop = list.scrollHeight;
    };

    const loadMessages = async () => {
        try {
            const response = await fetch(list.dataset.messagesUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!response.ok) return;
            const data = await response.json();
            appendMessages(data.messages || []);
        } catch (_) {
            // A later polling attempt can recover from a temporary network failure.
        }
    };

    form.addEventListener('submit', async event => {
        event.preventDefault();
        error.textContent = '';
        submit.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'Không thể gửi tin nhắn.');
            }
            input.value = '';
            await loadMessages();
        } catch (exception) {
            error.textContent = exception.message;
        } finally {
            submit.disabled = false;
            input.focus();
        }
    });

    loadMessages();
    window.setInterval(loadMessages, 3000);
});
</script>
@endsection