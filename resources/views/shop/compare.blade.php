@extends('layouts.shop')

@section('content')
<style>
    .compare-shell { max-width: 1320px; }
    .compare-table th { width: 190px; background: #f8f9fc; color: #344054; }
    .compare-product { min-width: 230px; }
    .compare-product img { height: 150px; object-fit: contain; }
    .compare-value { min-width: 230px; vertical-align: middle; }
</style>

<div class="container compare-shell my-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-danger small fw-bold text-uppercase">Đối chiếu sản phẩm</div>
            <h1 class="h3 fw-bold mb-1">So sánh thông số điều hòa</h1>
            <p class="text-muted mb-0">Tối đa 4 sản phẩm, thông số được lấy từ dữ liệu kỹ thuật bạn đã nhập.</p>
        </div>
        <a href="{{ route('shop.index') }}" class="btn btn-outline-danger"><i class="fa-solid fa-plus me-1"></i> Thêm sản phẩm</a>
    </div>

    @if($products->isEmpty())
        <div class="bg-white border rounded-4 shadow-sm text-center py-5">
            <i class="fa-solid fa-scale-balanced display-4 text-muted mb-3"></i>
            <h2 class="h5 fw-bold">Chưa có sản phẩm để so sánh</h2>
            <p class="text-muted">Hãy quay lại danh sách và bấm “So sánh” ở các sản phẩm bạn quan tâm.</p>
            <a href="{{ route('shop.index') }}" class="btn btn-danger">Xem sản phẩm</a>
        </div>
    @else
        <div class="table-responsive bg-white border rounded-4 shadow-sm">
            <table class="table table-bordered align-middle mb-0 compare-table">
                <thead>
                    <tr>
                        <th class="p-3">Thông số</th>
                        @foreach($products as $product)
                            @php
                                $variant = $product->variants->first();
                                $image = $product->primary_image_path ?? $product->image;
                            @endphp
                            <th class="compare-product p-3 text-center">
                                @if($image)
                                    <img src="{{ request()->getBaseUrl() . '/storage/' . $image }}" alt="{{ $product->name }}" class="w-100">
                                @endif
                                <a href="{{ route('shop.detail', $product->id) }}" class="d-block text-dark fw-bold text-decoration-none mt-2">{{ $product->name }}</a>
                                <div class="text-danger fw-bold mt-2">{{ number_format((float) ($variant?->price ?? $product->price), 0, ',', '.') }} đ</div>
                                <form action="{{ route('shop.compare.remove', $product->id) }}" method="POST" class="mt-2" data-ajax-toast data-compare-remove>
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger border" type="submit"><i class="fa-solid fa-xmark me-1"></i>Bỏ chọn</button>
                                </form>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="p-3">Thương hiệu</th>
                        @foreach($products as $product)
                            <td class="compare-value p-3 text-center">{{ $product->brand ?: '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th class="p-3">Công suất</th>
                        @foreach($products as $product)
                            <td class="compare-value p-3 text-center">{{ $product->variants->first()?->capacity_name ?? '—' }}</td>
                        @endforeach
                    </tr>
                    @foreach($specKeys as $specKey)
                        <tr>
                            <th class="p-3">{{ str_replace('_', ' ', ucfirst($specKey)) }}</th>
                            @foreach($products as $product)
                                @php
                                    $specs = $product->variants->first()?->specifications;
                                    $specs = is_array($specs) ? $specs : [];
                                @endphp
                                <td class="compare-value p-3 text-center">{{ $specs[$specKey] ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
