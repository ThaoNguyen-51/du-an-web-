<!doctype html>
<html lang="vi">
<head><meta charset="utf-8"><title>Hóa đơn #{{ $order->id }}</title>
<style>body{font:14px Arial;color:#172033;max-width:850px;margin:30px auto}h1{margin-bottom:4px}.muted{color:#687386}table{width:100%;border-collapse:collapse;margin-top:24px}th,td{padding:10px;border-bottom:1px solid #ddd;text-align:left}th:last-child,td:last-child{text-align:right}.total{text-align:right;font-size:18px;font-weight:bold;margin-top:18px}@media print{.no-print{display:none}}</style>
</head><body>
<div class="no-print"><button onclick="window.print()">In hóa đơn</button> <button onclick="window.close()">Đóng</button></div>
<h1>HÓA ĐƠN BÁN HÀNG</h1><div class="muted">Mã đơn: {{ $order->order_code ?: '#'.$order->id }} · {{ optional($order->created_at)->format('d/m/Y H:i') }}</div>
<hr><p><strong>Khách hàng:</strong> {{ $order->customer_name }}<br><strong>Điện thoại:</strong> {{ $order->customer_phone }}<br><strong>Địa chỉ:</strong> {{ $order->customer_address }}</p>
<table><thead><tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>
@foreach($order->items as $item)<tr><td>{{ $item->product_name }} {{ $item->capacity_name ? '('.$item->capacity_name.')' : '' }}</td><td>{{ $item->quantity }}</td><td>{{ number_format((float)$item->price, 0, ',', '.') }} đ</td><td>{{ number_format((float)($item->subtotal ?? $item->price * $item->quantity), 0, ',', '.') }} đ</td></tr>@endforeach
</tbody></table><div class="total">Tổng thanh toán: {{ number_format((float)$order->total_amount, 0, ',', '.') }} đ</div>
<p class="muted">Phương thức thanh toán: {{ strtoupper($order->payment_method ?: 'N/A') }} · Trạng thái: {{ $order->status_label }}</p>
</body></html>
