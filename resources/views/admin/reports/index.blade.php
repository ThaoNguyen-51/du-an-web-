@extends('layouts.shop')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-chart-line text-danger me-2"></i>Báo cáo quản trị</h3>
            <p class="text-muted mb-0">Doanh thu chỉ tính đơn đã thanh toán, không tính đơn đã hủy.</p>
        </div>
        <a href="{{ route('admin.reports.charts') }}" class="btn btn-danger"><i class="fa-solid fa-chart-pie me-1"></i>Xem biểu đồ</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted">Tổng số đơn hàng</div><div class="fs-3 fw-bold">{{ number_format($totalOrders) }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted">Tổng số khách hàng</div><div class="fs-3 fw-bold">{{ number_format($totalCustomers) }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted">Tổng doanh thu</div><div class="fs-3 fw-bold text-success">{{ number_format($totalRevenue, 0, ',', '.') }} đ</div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Doanh thu theo sản phẩm</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Sản phẩm</th><th class="text-end">Số lượng bán</th><th class="text-end">Doanh thu</th></tr></thead>
                <tbody>
                    @forelse($productRevenue as $row)
                        <tr><td>{{ $row->product_name }}</td><td class="text-end">{{ number_format($row->quantity) }}</td><td class="text-end">{{ number_format($row->revenue, 0, ',', '.') }} đ</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Chưa có doanh thu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach([
        ['Doanh thu theo ngày', 'Ngày', $dailyRevenue],
        ['Doanh thu theo tháng', 'Tháng', $monthlyRevenue],
        ['Doanh thu theo năm', 'Năm', $yearlyRevenue],
    ] as [$title, $label, $rows])
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">{{ $title }}</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead><tr><th>{{ $label }}</th><th class="text-end">Số đơn đã thanh toán</th><th class="text-end">Doanh thu</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr><td>{{ $row->label }}</td><td class="text-end">{{ number_format($row->order_count) }}</td><td class="text-end">{{ number_format($row->revenue, 0, ',', '.') }} đ</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Chưa có doanh thu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Doanh thu theo phương thức thanh toán</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0"><thead><tr><th>Phương thức</th><th class="text-end">Doanh thu</th></tr></thead><tbody>
                @forelse($paymentRevenue as $row)<tr><td>{{ $row->method }}</td><td class="text-end">{{ number_format($row->revenue, 0, ',', '.') }} đ</td></tr>@empty
                    <tr><td colspan="2" class="text-center text-muted py-4">Chưa có doanh thu.</td></tr>
                @endforelse
            </tbody></table>
        </div>
    </div>
</div>
@endsection
