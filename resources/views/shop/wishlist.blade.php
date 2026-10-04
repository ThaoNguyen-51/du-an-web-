@extends('layouts.shop')

@section('content')
<div class="container py-4">
    <div class="mb-4"><div class="small text-danger fw-bold text-uppercase">Cá nhân hóa</div><h1 class="h3 fw-bold">Danh sách yêu thích</h1><p class="text-muted mb-0">Bạn sẽ thấy cảnh báo khi sản phẩm giảm giá hoặc có hàng trở lại.</p></div>
    @if($items->isEmpty())
        <div class="card border-0 shadow-sm p-5 text-center"><i class="fa-regular fa-heart fs-1 text-danger mb-3"></i><h5>Danh sách yêu thích đang trống</h5><a href="{{ route('shop.index') }}" class="btn btn-hc mt-2">Khám phá sản phẩm</a></div>
    @else
        <div class="row g-4">
            @foreach($items as $item)
                <div class="col-md-6 col-xl-4">
                    <article class="card h-100 border-0 shadow-sm overflow-hidden">
                        <img src="{{ $item->product->primary_image_path ? asset('storage/'.$item->product->primary_image_path) : asset('images/no-image.png') }}" class="card-img-top" style="height:210px;object-fit:cover" alt="{{ $item->product->name }}">
                        <div class="card-body d-flex flex-column">
                            @if($item->price_dropped)<div class="alert alert-success py-2 small"><i class="fa-solid fa-arrow-trend-down me-1"></i>Sản phẩm đã giảm giá.</div>@endif
                            @if($item->back_in_stock)<div class="alert alert-info py-2 small"><i class="fa-solid fa-bell me-1"></i>Sản phẩm đã có hàng trở lại.</div>@endif
                            <h5 class="h6 fw-bold">{{ $item->product->name }}</h5>
                            <div class="text-danger fw-bold mb-3">{{ number_format($item->current_price, 0, ',', '.') }}đ</div>
                            <div class="mt-auto d-flex gap-2"><a href="{{ route('shop.detail', $item->product->id) }}" class="btn btn-sm btn-outline-danger flex-grow-1">Xem sản phẩm</a><form action="{{ route('wishlist.toggle', $item->product) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-secondary" title="Bỏ yêu thích"><i class="fa-solid fa-trash"></i></button></form></div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
