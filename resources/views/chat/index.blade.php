@extends('layouts.shop')

@section('content')
<style>
    .chat-layout { height: 100vh; height: 100dvh; overflow: hidden; }
    .admin-shell.chat-layout .admin-main { height: 100vh; height: 100dvh; min-height: 0; }
    .chat-layout-main { flex: 1 1 auto; min-height: 0; overflow: hidden; padding: 0 !important; }
    .admin-shell .admin-chat-content { height: calc(100vh - 76px); height: calc(100dvh - 76px); min-height: 0; padding: 0; overflow: hidden; }
    .chat-page { --chat-ink: #252c35; --chat-muted: #89919b; --chat-line: #e7e9ec; --chat-red: #ee4d2d; height: 100%; min-height: 0; background: #f4f5f7; padding: 16px 0; }
    .chat-page > .container-fluid { height: 100%; min-height: 0; }
    .chat-shell { max-width: 1240px; height: 100%; min-height: 0; margin: 0 auto; display: grid; grid-template-columns: 290px minmax(0, 1fr); grid-template-rows: minmax(0, 1fr); overflow: hidden; background: #fff; border: 1px solid var(--chat-line); box-shadow: 0 12px 35px rgba(24, 32, 43, .08); }
    .chat-shell.admin-chat-shell { grid-template-columns: 250px minmax(0, 1fr) 280px; }
    .chat-sidebar { min-width: 0; border-right: 1px solid var(--chat-line); display: flex; flex-direction: column; background: #fff; }
    .chat-sidebar-head { padding: 20px 18px 14px; border-bottom: 1px solid var(--chat-line); }
    .chat-sidebar-head h1 { font-size: 1.1rem; font-weight: 750; margin: 0 0 12px; color: var(--chat-ink); }
    .chat-search .form-control { font-size: .85rem; border-color: #e2e5e9; box-shadow: none; }
    .chat-list { overflow-y: auto; flex: 1; }
    .chat-contact { display: flex; gap: 11px; align-items: center; min-height: 72px; padding: 12px 16px; border-bottom: 1px solid #f0f1f3; color: inherit; text-decoration: none; }
    .chat-contact:hover, .chat-contact.active { background: #fff5f2; }
    .chat-avatar { width: 42px; height: 42px; flex: 0 0 42px; border-radius: 50%; display: grid; place-items: center; background: #ffede8; color: var(--chat-red); font-weight: 750; }
    .chat-avatar img { width: 100%; height: 100%; border-radius: inherit; object-fit: cover; }
    .chat-contact-name { font-size: .88rem; font-weight: 700; color: var(--chat-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .chat-contact-meta { color: var(--chat-muted); font-size: .76rem; margin-top: 3px; }
    .chat-unread-badge { min-width: 20px; padding: 3px 6px; border-radius: 999px; background: var(--chat-red); color: #fff; font-size: .68rem; font-weight: 700; text-align: center; }
    .chat-filter { display:flex; gap:6px; margin-top:10px; }
    .chat-filter a { flex:1; padding:6px 7px; border:1px solid #e2e5e9; border-radius:7px; color:var(--chat-muted); font-size:.72rem; text-align:center; text-decoration:none; }
    .chat-filter a.active, .chat-filter a:hover { color:var(--chat-red); border-color:#f2a18f; background:#fff5f2; }
    .chat-main { min-width: 0; min-height: 0; overflow: hidden; display: flex; flex-direction: column; }
    .chat-top { padding: 15px 20px; min-height: 68px; border-bottom: 1px solid var(--chat-line); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .chat-top-title { font-weight: 750; color: var(--chat-ink); }
    .chat-top-subtitle { font-size: .75rem; color: var(--chat-muted); margin-top: 2px; }
    .chat-order-search { padding: 9px 18px; background: #fbfbfc; border-bottom: 1px solid var(--chat-line); }
    .chat-order-search .form-control, .chat-order-search .btn { font-size: .76rem; }
    .chat-messages { flex: 1 1 auto; min-height: 0; overflow-x: hidden; overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; touch-action: pan-y; scrollbar-gutter: stable; display: flex; flex-direction: column; gap: 12px; padding: 22px clamp(16px, 4vw, 52px); background: #fff; }
    .chat-day { align-self: center; padding: 5px 10px; background: #f1f2f4; color: #858c95; border-radius: 3px; font-size: .7rem; }
    .chat-row { display: flex; flex-direction: column; align-items: flex-start; max-width: 78%; }
    .chat-row.mine { align-self: flex-end; align-items: flex-end; }
    .chat-sender { color: var(--chat-muted); font-size: .7rem; margin: 0 4px 4px; }
    .chat-bubble { background: #f3f4f6; color: var(--chat-ink); border-radius: 3px 14px 14px 14px; padding: 10px 13px; font-size: .88rem; line-height: 1.45; overflow-wrap: anywhere; }
    .chat-row.mine .chat-bubble { background: #fff1ed; border-radius: 14px 3px 14px 14px; }
    .chat-time { color: var(--chat-muted); font-size: .68rem; margin: 4px 3px 0; }
    .chat-empty { flex: 1; display: grid; place-content: center; text-align: center; padding: 24px; color: var(--chat-muted); }
    .chat-empty i { font-size: 2.3rem; color: #d7dbe0; margin-bottom: 12px; }
    .chat-context-card { display: flex; align-items: center; gap: 9px; max-width: 260px; margin-top: 6px; padding: 7px; border: 1px solid #e8e9eb; background: #fff; color: #424a55; font-size: .74rem; }
    .chat-context-card img { width: 38px; height: 38px; object-fit: contain; }
    .chat-context-card strong, .chat-context-card small { display: block; }
    .chat-context-card small { color: var(--chat-muted); margin-top: 2px; }
    .chat-compose { border-top: 1px solid var(--chat-line); padding: 14px 20px 16px; background: #fff; }
    .chat-compose textarea { min-height: 62px; resize: vertical; border: 0; box-shadow: none !important; font-size: .9rem; }
    .chat-attachments { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; padding: 7px 0 10px; }
    .chat-attachment { display: inline-flex; align-items: center; gap: 7px; min-height: 34px; padding: 3px 9px; border: 1px solid #e5e7eb; border-radius: 4px; color: #657080; background: #fff; }
    .chat-attachment i { color: var(--chat-red); font-size: .82rem; }
    .chat-attachment select { max-width: 230px; border: 0; outline: 0; color: #414955; background: transparent; font-size: .76rem; }
    .chat-compose-foot { border-top: 1px solid #f0f1f3; padding-top: 10px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .chat-compose-hint { color: var(--chat-muted); font-size: .72rem; }
    .chat-quick-reply { max-width: 250px; border: 0; color: #67717e; font-size: .75rem; background: transparent; }
    .chat-send { background: var(--chat-red); border: 0; color: #fff; font-weight: 700; padding: 8px 18px; border-radius: 3px; }
    .chat-send:hover { background: #d94427; color: #fff; }
    .chat-no-selection { flex: 1; display: grid; place-content: center; text-align: center; color: var(--chat-muted); padding: 24px; }
    .chat-info-panel { min-width: 0; min-height: 0; overflow-y: auto; border-left: 1px solid var(--chat-line); padding: 18px 14px; background: #fbfbfc; }
    .chat-info-title { color: #424a55; font-size: .77rem; font-weight: 800; text-transform: uppercase; margin: 0 0 10px; }
    .chat-customer-card { padding: 12px; margin-bottom: 18px; border: 1px solid var(--chat-line); background: #fff; }
    .chat-order-card { display: block; padding: 10px; margin-bottom: 8px; border: 1px solid var(--chat-line); background: #fff; color: inherit; text-decoration: none; }
    .chat-order-card:hover, .chat-order-card.active { border-color: #f2a18f; background: #fff5f2; }
    .chat-order-card small { color: var(--chat-muted); display: block; margin-top: 3px; }
    @media (max-width: 767.98px) {
        .chat-page { padding: 0; }
        .chat-shell { height: 100%; min-height: 0; grid-template-columns: 92px minmax(0, 1fr); border-left: 0; border-right: 0; }
        .chat-shell.admin-chat-shell { grid-template-columns: 64px minmax(0, 1fr); }
        .chat-info-panel { display: none; }
        .chat-sidebar-head { padding: 14px 8px; }
        .chat-sidebar-head h1 { font-size: .78rem; text-align: center; }
        .chat-search .form-control, .chat-search .input-group-text { padding-left: 7px; padding-right: 7px; font-size: .7rem; }
        .chat-contact { justify-content: center; padding: 11px 5px; }
        .chat-contact-copy { display: none; }
        .chat-avatar { width: 42px; height: 42px; }
        .chat-top { padding: 12px; }
        .chat-messages { padding: 16px 12px; }
        .chat-row { max-width: 92%; }
        .chat-compose { padding: 10px 12px; }
        .chat-attachment select { max-width: min(30vw, 150px); }
        .chat-compose-hint { display: none; }
    }
</style>

<div class="chat-page">
    <div class="container-fluid px-0 px-md-3">
        <section class="chat-shell {{ $isAdmin ? 'admin-chat-shell' : '' }}" aria-label="Trung tâm chat">
            <aside class="chat-sidebar">
                <div class="chat-sidebar-head">
                    <h1>{{ $isAdmin ? 'Tin nhắn khách hàng' : 'Tin nhắn' }}</h1>
                    @if($isAdmin)
                        <form action="{{ route('admin.chat.index') }}" method="GET" class="chat-search">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="search" name="keyword" value="{{ $keyword }}" class="form-control border-start-0" placeholder="Tên, mã đơn, tin nhắn..." aria-label="Tìm khách hàng">
                            </div>
                        </form>
                        <div class="chat-filter">
                            <a class="{{ !$unreadOnly ? 'active' : '' }}" href="{{ route('admin.chat.index', $keyword ? ['keyword' => $keyword] : []) }}">Tất cả</a>
                            <a class="{{ $unreadOnly ? 'active' : '' }}" href="{{ route('admin.chat.index', array_filter(['keyword' => $keyword ?: null, 'unread' => 1])) }}">Chưa đọc</a>
                        </div>
                    @else
                        <div class="small text-muted">Tư vấn và hỗ trợ</div>
                    @endif
                </div>
                <div class="chat-list">
                    @if($isAdmin)
                        @forelse($customers as $customer)
                            <a class="chat-contact {{ $selectedCustomer?->id === $customer->id ? 'active' : '' }}" href="{{ route('admin.chat.index', array_filter(['customer_id' => $customer->id, 'keyword' => $keyword ?: null, 'unread' => $unreadOnly ? 1 : null])) }}">
                                <span class="chat-avatar">{{ mb_substr($customer->name, 0, 1) }}</span>
                                <span class="chat-contact-copy min-w-0 flex-grow-1"><span class="chat-contact-name d-block">{{ $customer->name }}</span><span class="chat-contact-meta d-block">Mở cuộc trò chuyện</span></span>
                                @if($unreadCustomerIds->contains($customer->id))<span class="chat-unread-badge">Mới</span>@endif
                            </a>
                        @empty
                            <div class="p-3 small text-muted">Không tìm thấy khách hàng phù hợp.</div>
                        @endforelse
                    @else
                        <div class="chat-contact active">
                            <span class="chat-avatar"><i class="fa-solid fa-store"></i></span>
                            <span class="chat-contact-copy min-w-0"><span class="chat-contact-name d-block">HC Electric</span><span class="chat-contact-meta d-block">Shop hỗ trợ trực tuyến</span></span>
                        </div>
                    @endif
                </div>
            </aside>

            <div class="chat-main">
                @if($selectedCustomer)
                    <header class="chat-top">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span class="chat-avatar d-none d-sm-grid">{{ $isAdmin ? mb_substr($selectedCustomer->name, 0, 1) : 'H' }}</span>
                            <div class="min-w-0">
                                <div class="chat-top-title text-truncate">{{ $isAdmin ? $selectedCustomer->name : 'HC Electric' }}</div>
                                <div class="chat-top-subtitle">Tư vấn và hỗ trợ · hội thoại chung</div>
                            </div>
                        </div>
                    </header>

                    <div class="chat-messages" id="chat-messages">
                        @forelse($messages as $message)
                            @php($isMine = (int) $message->sender_id === (int) auth()->id())
                            <div class="chat-row {{ $isMine ? 'mine' : '' }}">
                                <div class="chat-sender">{{ $isAdmin ? ($message->sender_role === 'customer' ? ($message->sender?->name ?? 'Khách hàng') : ($message->sender_role === 'staff' ? ($message->sender?->name ?? 'Nhân viên') : 'Admin')) : ($message->sender_role === 'customer' ? 'Bạn' : 'Shop') }}</div>
                                <div class="chat-bubble">{{ $message->message }}</div>
                                @if($message->order_id)
                                    <div class="chat-context-card"><i class="fa-solid fa-bag-shopping text-danger"></i><span><strong>Đơn #{{ $message->order_id }}</strong><small>{{ $message->order?->status_label ?? 'Trao đổi về đơn hàng' }}{{ $message->order ? ' · ' . number_format((float) $message->order->total_amount, 0, ',', '.') . ' đ' : '' }}</small></span></div>
                                @endif
                                @if($message->product?->name)
                                    <div class="chat-context-card">
                                        @if($message->product->image)<img src="{{ asset('storage/' . $message->product->image) }}" alt="{{ $message->product->name }}">@else<i class="fa-solid fa-snowflake text-danger px-2"></i>@endif
                                        <span><strong>{{ $message->product->name }}</strong><small>{{ $message->product->brand }} · {{ number_format((float) $message->product->price, 0, ',', '.') }} đ</small></span>
                                    </div>
                                @endif
                                <div class="chat-time">{{ $message->created_at?->format('H:i · d/m/Y') }}</div>
                            </div>
                        @empty
                            <div class="chat-empty"><div><i class="fa-regular fa-comments d-block"></i><strong>{{ $selectedOrder ? 'Bắt đầu trao đổi về đơn hàng này' : ($selectedProduct ? 'Hỏi shop về sản phẩm này' : 'Bắt đầu trò chuyện với shop') }}</strong><div class="small mt-1">Tin nhắn của bạn sẽ được gửi trực tiếp đến bộ phận hỗ trợ.</div></div></div>
                        @endforelse
                    </div>

                    <form action="{{ route($isAdmin ? 'admin.chat.send' : 'chat.send') }}" method="POST" class="chat-compose">
                        @csrf
                        @if($isAdmin)<input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">@endif
                        @if($isAdmin && $keyword !== '')<input type="hidden" name="keyword" value="{{ $keyword }}">@endif
                        @if($isAdmin && $orderSearch !== '')<input type="hidden" name="order_search" value="{{ $orderSearch }}">@endif
                        <textarea name="message" class="form-control" maxlength="2000" placeholder="Nhập tin nhắn..." required>{{ old('message') }}</textarea>
                        <div class="chat-attachments" aria-label="Đính kèm thông tin">
                            <label class="chat-attachment" title="Gắn đơn hàng vào tin nhắn">
                                <i class="fa-solid fa-bag-shopping"></i>
                                <select name="order_id" aria-label="Chọn đơn hàng để đính kèm">
                                    <option value="">Đơn hàng</option>
                                    @foreach($orders as $order)
                                        <option value="{{ $order->id }}" @selected($selectedOrder?->id === $order->id)>#{{ $order->id }} · {{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="chat-attachment" title="Gắn sản phẩm vào tin nhắn">
                                <i class="fa-solid fa-box-open"></i>
                                <select name="product_id" aria-label="Chọn sản phẩm để đính kèm">
                                    <option value="">Sản phẩm</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" @selected($selectedProduct?->id === $product->id)>{{ $product->name }} · {{ $product->brand }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <div class="chat-compose-foot">
                            @if($isAdmin)
                                <select class="chat-quick-reply" id="chat-quick-reply" aria-label="Chọn mẫu tin nhắn nhanh">
                                    <option value="">Mẫu trả lời nhanh</option>
                                    <option data-reply="Chào anh/chị, shop đã nhận được tin nhắn và sẽ hỗ trợ mình ngay ạ.">Chào hỏi khách hàng</option>
                                    <option data-reply="Anh/chị vui lòng cho shop biết model máy, diện tích phòng và tình trạng đang gặp phải để shop tư vấn chính xác nhé.">Tư vấn kỹ thuật điều hòa</option>
                                    <option data-reply="Anh/chị thử kiểm tra chế độ làm mát, nhiệt độ cài đặt và vệ sinh lưới lọc. Nếu máy vẫn chưa mát, shop sẽ hỗ trợ kiểm tra thêm ạ.">Máy làm mát yếu</option>
                                    <option data-reply="Anh/chị vui lòng gửi mã đơn hàng hoặc số điện thoại đặt hàng để shop kiểm tra tiến độ giúp mình nhé.">Tra cứu đơn hàng</option>
                                    <option data-reply="Shop đã ghi nhận yêu cầu lắp đặt/bảo hành. Anh/chị vui lòng cho shop xin khu vực và thời gian thuận tiện để hỗ trợ ạ.">Lắp đặt và bảo hành</option>
                                </select>
                            @else
                                <span class="chat-compose-hint">Nhấn gửi để trao đổi với shop</span>
                            @endif
                            <button type="submit" class="chat-send"><i class="fa-solid fa-paper-plane me-2"></i>Gửi</button>
                        </div>
                    </form>
                @else
                    <div class="chat-no-selection"><div><i class="fa-regular fa-comments d-block fs-1 mb-3"></i><strong>{{ $isAdmin ? 'Chọn khách hàng để bắt đầu tư vấn' : 'Tin nhắn với HC Electric' }}</strong>@unless($isAdmin)<div class="mt-2">Mọi trao đổi sẽ hiển thị chung trong cuộc trò chuyện này.</div>@endunless</div></div>
                @endif
            </div>

            @if($isAdmin && $selectedCustomer)
                <aside class="chat-info-panel">
                    <section class="chat-customer-card">
                        <h2 class="chat-info-title">Thông tin khách hàng</h2>
                        <strong class="d-block">{{ $selectedCustomer->name }}</strong>
                        <small class="text-muted d-block mt-1">{{ $selectedCustomer->email }}</small>
                        @if($latestCustomerOrder?->customer_phone)
                            <small class="text-muted d-block mt-1"><i class="fa-solid fa-phone me-1"></i>{{ $latestCustomerOrder->customer_phone }}</small>
                        @endif
                    </section>
                    <section>
                        <h2 class="chat-info-title">Đơn hàng gần đây</h2>
                        <form action="{{ route('admin.chat.index') }}" method="GET" class="mb-3">
                            <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                            @if($keyword !== '')<input type="hidden" name="keyword" value="{{ $keyword }}">@endif
                            <div class="input-group input-group-sm">
                                <input type="search" name="order_search" value="{{ $orderSearch }}" class="form-control" placeholder="Tên, SĐT hoặc mã đơn..." aria-label="Tìm đơn của khách">
                                <button class="btn btn-outline-secondary" type="submit" title="Tìm đơn"><i class="fa-solid fa-magnifying-glass"></i></button>
                            </div>
                        </form>
                        @forelse($orders as $order)
                            <div class="chat-order-card {{ $selectedOrder?->id === $order->id ? 'active' : '' }}">
                                <strong>Đơn #{{ $order->id }}</strong>
                                <small>{{ $order->customer_name }} · {{ $order->customer_phone }}</small>
                                <small>{{ $order->status_label }} · {{ number_format((float) $order->total_amount, 0, ',', '.') }} đ</small>
                                <div class="d-flex gap-2 mt-1 small"><a href="{{ route('admin.chat.index', array_filter(['customer_id' => $selectedCustomer->id, 'order_id' => $order->id, 'order_search' => $orderSearch ?: null, 'keyword' => $keyword ?: null, 'unread' => $unreadOnly ? 1 : null])) }}" class="text-danger">Trao đổi</a><a href="{{ route('admin.orders.show', $order->id) }}" target="_blank" class="text-danger">Xem chi tiết</a></div>
                            </div>
                        @empty
                            <div class="small text-muted">Không tìm thấy đơn hàng.</div>
                        @endforelse
                    </section>
                </aside>
            @endif
        </section>
        @if(session('success'))<div class="alert alert-success border-0 shadow-sm mt-3 mb-0">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger border-0 shadow-sm mt-3 mb-0">{{ $errors->first() }}</div>@endif
    </div>
</div>
<script>
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) requestAnimationFrame(() => chatMessages.scrollTop = chatMessages.scrollHeight);

    const quickReply = document.getElementById('chat-quick-reply');
    if (quickReply) {
        quickReply.addEventListener('change', () => {
            const response = quickReply.selectedOptions[0]?.dataset.reply;
            if (!response) return;
            const messageInput = document.querySelector('.chat-compose textarea[name="message"]');
            messageInput.value = response;
            messageInput.focus();
            quickReply.selectedIndex = 0;
        });
    }
</script>
@endsection
