@extends('layouts.shop')

@section('content')
<div class="container my-4">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>Quản Lý Sản Phẩm Điều Hòa
            </h3>
            <p class="text-muted small mb-0">Quản lý thông tin, giá cả và biến thể công suất sản phẩm trên hệ thống</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-list-check me-1"></i> Đơn hàng
            </a>
            <a href="{{ route('air_conditioners.create') }}" class="btn btn-danger fw-bold">
                <i class="fa-solid fa-plus me-1"></i> Thêm sản phẩm
            </a>
        </div>
    </div>

    <!-- Thông báo Alert -->
    @if($lowStockCount > 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2 mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Có <strong>{{ $lowStockCount }}</strong> biến thể sắp hết hàng (từ 1 đến 5 sản phẩm).</span>
            @if(request('stock') === 'low')
                <a href="{{ route('air_conditioners.index') }}" class="ms-auto small fw-semibold text-dark">Xem tất cả sản phẩm</a>
            @else
                <a href="{{ route('air_conditioners.index', ['stock' => 'low']) }}" class="ms-auto small fw-semibold text-dark">Xem hàng sắp hết</a>
            @endif
        </div>
    @elseif(request('stock') === 'out')
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-4">
            <i class="fa-solid fa-circle-xmark"></i>
            <span>Đang hiển thị các biến thể đã hết hàng.</span>
            <a href="{{ route('air_conditioners.index') }}" class="ms-auto small fw-semibold text-dark">Xem tất cả</a>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tìm kiếm & Lọc -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('air_conditioners.index') }}" method="GET" class="row g-2">
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="keyword" class="form-control border-start-0" placeholder="Tìm kiếm theo tên sản phẩm..." value="{{ request('keyword') }}">
                    </div>
                </div>
                <div class="col-lg-2">
                    <select name="brand" class="form-select">
                        <option value="">-- Tất cả thương hiệu --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" @selected(request('brand') == $brand)>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2"><select name="stock" class="form-select"><option value="">-- Tồn kho --</option><option value="low" @selected(request('stock') === 'low')>Sắp hết (1-5)</option><option value="out" @selected(request('stock') === 'out')>Hết hàng</option><option value="available" @selected(request('stock') === 'available')>Còn nhiều (&gt;5)</option></select></div>
                <div class="col-lg-2"><select name="sort" class="form-select"><option value="">Mới cập nhật</option><option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A-Z</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Giá thấp đến cao</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Giá cao đến thấp</option><option value="stock_asc" @selected(request('sort') === 'stock_asc')>Tồn kho thấp trước</option></select></div>
                <div class="col-lg-2 d-flex gap-2"><button type="submit" class="btn btn-secondary flex-grow-1">Lọc</button><a href="{{ route('air_conditioners.index') }}" class="btn btn-light border" title="Xóa bộ lọc"><i class="fa-solid fa-rotate-left"></i></a></div>
            </form>
        </div>
    </div>

    <!-- Bảng sản phẩm -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">STT</th>
                            <th style="width: 70px;">Hình ảnh</th>
                            <th>Thông tin sản phẩm</th>
                            <th>Thương hiệu</th>
                            <th>Giá niêm yết</th>
                            <th>Các phiên bản công suất (BTU)</th>
                            <th class="text-center" style="width: 130px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($airConditioners as $index => $item)
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="border rounded p-1 bg-white text-center" style="width: 50px; height: 50px;">
                                        @php $productImage = $item->primary_image_path ?? $item->image; @endphp
                                        @if($productImage)
                                            <img src="{{ filter_var($productImage, FILTER_VALIDATE_URL) ? $productImage : request()->getBaseUrl() . '/storage/' . $productImage }}" class="w-100 h-100" style="object-fit: contain;">
                                        @else
                                            <i class="fa-regular fa-image fs-4 text-muted mt-1"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('air_conditioners.show', $item->id) }}" class="fw-bold text-dark text-decoration-none hover-primary d-block">
                                        {{ $item->name }}
                                    </a>
                                    <small class="text-muted">Mã: HC-{{ str_pad($item->id, 6, '0', STR_PAD_LEFT) }}</small>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary border">{{ $item->brand }}</span></td>
                                <td class="fw-bold text-danger">{{ number_format($item->price, 0, ',', '.') }}đ</td>
                                <td>
                                    @if($item->variants && $item->variants->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($item->variants as $variant)
                                                <span class="badge {{ $variant->stock <= 0 ? 'text-bg-danger' : ($variant->stock <= 5 ? 'text-bg-warning' : 'bg-light text-dark') }} border">
                                                    {{ $variant->capacity_name }}: <strong class="text-danger">{{ number_format($variant->price, 0, ',', '.') }}đ</strong>
                                                    @if($variant->stock <= 5)<small class="ms-1">({{ $variant->stock <= 0 ? 'hết hàng' : $variant->stock . ' còn' }})</small>@endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">Chưa có phiên bản</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('air_conditioners.show', $item->id) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('air_conditioners.edit', $item->id) }}" class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('air_conditioners.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Xác nhận xóa sản phẩm này?');" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-box-open fs-1 mb-2 d-block text-secondary"></i>
                                    Không tìm thấy dữ liệu điều hòa nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection