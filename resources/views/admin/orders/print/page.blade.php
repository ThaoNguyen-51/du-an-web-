<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phiếu đơn hàng</title>
    <style>
        :root { --ink:#172033; --muted:#687386; --line:#e5e9f0; --accent:#e21b23; --soft:#f6f8fb; }
        * { box-sizing:border-box; }
        body { margin:0; background:#eef1f6; color:var(--ink); font:14px/1.5 Arial,sans-serif; }
        .toolbar { padding:14px 22px; background:#172033; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .toolbar-title { color:#fff; font-weight:700; }
        .toolbar-actions { display:flex; gap:8px; }
        .toolbar button,.toolbar a { border:1px solid rgba(255,255,255,.3); border-radius:8px; background:transparent; color:#fff; padding:9px 15px; text-decoration:none; cursor:pointer; font:inherit; }
        .toolbar .primary { background:var(--accent); border-color:var(--accent); }
        .sheet { max-width:900px; margin:28px auto; }
        .order { background:#fff; border-radius:16px; padding:34px 38px; margin-bottom:28px; box-shadow:0 12px 35px rgba(24,37,63,.1); break-after:page; }
        .order:last-child { break-after:auto; }
        .brand-row { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; padding-bottom:22px; border-bottom:2px solid var(--ink); }
        .brand { display:flex; align-items:center; gap:11px; font-size:20px; font-weight:800; }
        .brand-mark { display:grid; place-items:center; width:38px; height:38px; border-radius:11px; background:var(--accent); color:#fff; }
        .document-title { text-align:right; }
        .document-title h1 { margin:0; font-size:23px; letter-spacing:.06em; }
        .document-title p { margin:3px 0 0; color:var(--muted); font-size:12px; }
        .order-meta { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin:22px 0; }
        .meta-box,.info-card { background:var(--soft); border:1px solid var(--line); border-radius:10px; padding:12px 14px; }
        .meta-label,.section-label { color:var(--muted); text-transform:uppercase; letter-spacing:.08em; font-size:10px; font-weight:700; }
        .meta-value { display:block; margin-top:3px; font-weight:700; }
        .info-grid { display:grid; grid-template-columns:1.2fr .8fr; gap:14px; margin-bottom:22px; }
        .info-card h2 { margin:0 0 9px; font-size:13px; }
        .info-card p { margin:3px 0; }
        .info-card strong { font-size:16px; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th { background:var(--ink); color:#fff; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
        th,td { padding:12px 10px; text-align:left; border-bottom:1px solid var(--line); }
        th:first-child,td:first-child { padding-left:14px; }
        th:last-child,td:last-child { text-align:right; padding-right:14px; }
        .product-name { font-weight:700; }
        .product-variant { color:var(--muted); font-size:12px; margin-top:2px; }
        .summary { margin:20px 0 0 auto; max-width:330px; }
        .summary-row { display:flex; justify-content:space-between; gap:20px; padding:5px 0; color:var(--muted); }
        .summary-total { display:flex; justify-content:space-between; gap:20px; border-top:2px solid var(--ink); margin-top:8px; padding-top:12px; font-weight:800; font-size:18px; color:var(--accent); }
        .note { margin-top:22px; padding:12px 14px; border-left:4px solid #f0b429; background:#fff8df; border-radius:6px; }
        .footer { display:flex; justify-content:space-between; gap:20px; margin-top:30px; padding-top:16px; border-top:1px solid var(--line); color:var(--muted); font-size:12px; }
        @media (max-width:700px) {
            .toolbar { align-items:flex-start; flex-direction:column; }
            .sheet { margin:0; }
            .order { border-radius:0; padding:24px 18px; }
            .brand-row,.info-grid { grid-template-columns:1fr; display:block; }
            .document-title { text-align:left; margin-top:18px; }
            .order-meta { grid-template-columns:1fr; }
            table { font-size:12px; }
            th,td { padding:9px 5px; }
        }
        @page { size:A4; margin:12mm; }
        @media print {
            body { background:#fff; }
            .toolbar { display:none; }
            .sheet { max-width:none; margin:0; }
            .order { box-shadow:none; border-radius:0; padding:0; margin:0 0 12mm; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <div class="toolbar-title"><i>HC</i> · Xem trước phiếu đơn hàng</div>
    <div class="toolbar-actions">
        <button class="primary" onclick="window.print()">In / lưu PDF</button>
        <a href="{{ route('admin.orders.print.index') }}">Quay lại</a>
    </div>
</div>
<main class="sheet">
@foreach($orders as $order)
    @php
        $subtotal = $order->items->sum(fn ($item) => (float) ($item->subtotal ?? ($item->price * $item->quantity)));
        $shipping = (float) ($order->shipping_fee ?? 0);
        $discount = (float) ($order->discount_amount ?? 0);
    @endphp
    <article class="order">
        <header class="brand-row">
            <div class="brand"><span class="brand-mark">❄</span><span>HC ELECTRIC<small style="display:block;color:var(--muted);font-size:10px;font-weight:400;letter-spacing:.12em">ĐIỆN MÁY CHÍNH HÃNG</small></span></div>
            <div class="document-title"><h1>PHIẾU ĐƠN HÀNG</h1><p>Thông tin xử lý và giao hàng</p></div>
        </header>
        <div class="order-meta">
            <div class="meta-box"><span class="meta-label">Mã đơn</span><span class="meta-value">#{{ $order->id }}</span></div>
            <div class="meta-box"><span class="meta-label">Ngày đặt</span><span class="meta-value">{{ optional($order->created_at)->format('d/m/Y H:i') }}</span></div>
            <div class="meta-box"><span class="meta-label">Trạng thái</span><span class="meta-value">{{ $order->status_label }}</span></div>
        </div>
        <div class="info-grid">
            <section class="info-card"><h2>THÔNG TIN GIAO HÀNG</h2><strong>{{ $order->customer_name }}</strong><p>{{ $order->customer_phone }}</p><p>{{ $order->customer_address }}</p></section>
            <section class="info-card"><h2>THANH TOÁN</h2><p><b>Phương thức:</b> {{ strtoupper($order->payment_method ?: 'N/A') }}</p><p><b>Mã vận đơn:</b> {{ $order->ghn_order_code ?: ($order->virtual_tracking_code ?: 'Chưa có') }}</p></section>
        </div>
        <div class="section-label">Danh sách sản phẩm</div>
        <table>
            <thead><tr><th style="width:55%">Sản phẩm</th><th style="text-align:center;width:10%">SL</th><th style="text-align:right;width:17%">Đơn giá</th><th style="width:18%">Thành tiền</th></tr></thead>
            <tbody>
            @foreach($order->items as $item)
                <tr><td><div class="product-name">{{ $item->product_name }}</div>@if($item->capacity_name)<div class="product-variant">{{ $item->capacity_name }}</div>@endif</td><td style="text-align:center">{{ $item->quantity }}</td><td style="text-align:right">{{ number_format((float)$item->price, 0, ',', '.') }} đ</td><td>{{ number_format((float)($item->subtotal ?? $item->price * $item->quantity), 0, ',', '.') }} đ</td></tr>
            @endforeach
            </tbody>
        </table>
        <div class="summary">
            <div class="summary-row"><span>Tạm tính</span><b>{{ number_format($subtotal, 0, ',', '.') }} đ</b></div>
            <div class="summary-row"><span>Phí vận chuyển</span><b>{{ number_format($shipping, 0, ',', '.') }} đ</b></div>
            @if($discount > 0)<div class="summary-row"><span>Giảm giá</span><b>-{{ number_format($discount, 0, ',', '.') }} đ</b></div>@endif
            <div class="summary-total"><span>TỔNG CỘNG</span><span>{{ number_format((float)$order->total_amount, 0, ',', '.') }} đ</span></div>
        </div>
        @if($order->note)<div class="note"><b>Ghi chú đơn hàng:</b> {{ $order->note }}</div>@endif
        <footer class="footer"><span>Cảm ơn quý khách đã mua hàng tại HC Electric.</span><span>Người lập phiếu: __________________</span></footer>
    </article>
@endforeach
</main>
</body>
</html>
