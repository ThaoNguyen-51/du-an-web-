@extends('layouts.shop')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h3 class="fw-bold mb-1"><i class="fa-solid fa-chart-pie text-danger me-2"></i>Biểu đồ doanh thu</h3><p class="text-muted mb-0">Dữ liệu chỉ gồm các đơn đã thanh toán.</p></div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-table me-1"></i>Bảng số liệu</a>
    </div>
    <div class="row g-4">
        @foreach([
            ['productChart', 'Doanh thu theo sản phẩm', 'col-lg-6'],
            ['dailyChart', 'Doanh thu theo ngày', 'col-lg-6'],
            ['monthlyChart', 'Doanh thu theo tháng', 'col-lg-6'],
            ['yearlyChart', 'Doanh thu theo năm', 'col-lg-6'],
            ['paymentChart', 'Doanh thu theo phương thức thanh toán', 'col-lg-12'],
        ] as [$id, $title, $size])
            <div class="{{ $size }}"><div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold">{{ $title }}</div><div class="card-body" style="height: 340px"><canvas id="{{ $id }}"></canvas></div></div></div>
        @endforeach
    </div>
</div>
<div id="report-data" hidden data-value="{{ json_encode([
    'productLabels' => $productRevenue->pluck('product_name')->values(),
    'productValues' => $productRevenue->pluck('revenue')->values(),
    'dailyLabels' => $dailyRevenue->pluck('label')->values(),
    'dailyValues' => $dailyRevenue->pluck('revenue')->values(),
    'monthlyLabels' => $monthlyRevenue->pluck('label')->values(),
    'monthlyValues' => $monthlyRevenue->pluck('revenue')->values(),
    'yearlyLabels' => $yearlyRevenue->pluck('label')->values(),
    'yearlyValues' => $yearlyRevenue->pluck('revenue')->values(),
    'paymentLabels' => $paymentRevenue->pluck('method')->values(),
    'paymentValues' => $paymentRevenue->pluck('revenue')->values(),
]) }}"></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('DOMContentLoaded', function () {
    const data = JSON.parse(document.getElementById('report-data').dataset.value);
    const makeChart = (id, type, labels, values, label) => new Chart(document.getElementById(id), {
        type,
        data: { labels, datasets: [{ label, data: values, borderWidth: 2, tension: 0.3 }] },
        options: { responsive: true, maintainAspectRatio: false, scales: type === 'pie' ? {} : { y: { beginAtZero: true } } }
    });
    makeChart('productChart', 'bar', data.productLabels, data.productValues, 'Doanh thu (VNĐ)');
    makeChart('dailyChart', 'line', data.dailyLabels, data.dailyValues, 'Doanh thu (VNĐ)');
    makeChart('monthlyChart', 'bar', data.monthlyLabels, data.monthlyValues, 'Doanh thu (VNĐ)');
    makeChart('yearlyChart', 'bar', data.yearlyLabels, data.yearlyValues, 'Doanh thu (VNĐ)');
    makeChart('paymentChart', 'doughnut', data.paymentLabels, data.paymentValues, 'Doanh thu (VNĐ)');
});
</script>
@endsection
