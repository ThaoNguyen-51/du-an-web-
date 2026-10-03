@extends('layouts.shop')

@section('content')
<style>
    :root {
        --brand-red: #d71921;
        --brand-red-dark: #b51219;
        --brand-red-soft: #fff1f2;
        --text-dark: #1d2433;
        --text-muted: #667085;
        --line: #e9edf5;
        --surface: #ffffff;
        --surface-soft: #f7f8fc;
    }

    .bg-hc { background-color: var(--brand-red) !important; }
    .text-hc { color: var(--brand-red) !important; }
    .btn-hc {
        background: linear-gradient(135deg, var(--brand-red), var(--brand-red-dark));
        color: #fff;
        border: none;
        box-shadow: 0 10px 20px rgba(215, 25, 33, 0.18);
    }
    .btn-hc:hover {
        background: linear-gradient(135deg, var(--brand-red-dark), var(--brand-red));
        color: #fff;
    }

    .shop-shell {
        padding-bottom: 2rem;
    }

    .shop-banner {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #1b1f2e 0%, #d71921 55%, #ff5b53 100%);
        border-radius: 26px;
        box-shadow: 0 18px 40px rgba(27, 31, 46, 0.15);
        padding: 2.25rem 2rem;
    }

    .shop-banner::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255,255,255,0.28), transparent 32%);
    }

    .shop-banner-content {
        position: relative;
        z-index: 1;
    }

    .shop-banner .eyebrow {
        display: inline-flex;
        align-items: center;
        padding: .45rem .8rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        background: rgba(255,255,255,0.16);
        border: 1px solid rgba(255,255,255,0.2);
        backdrop-filter: blur(8px);
    }

    .shop-banner h1 {
        letter-spacing: -.03em;
        line-height: 1.1;
    }

    .shop-banner p {
        max-width: 680px;
        color: rgba(255,255,255,0.9);
    }

    .shop-filter {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 18px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
    }

    .filter-pill {
        border-radius: 999px;
        border: 1px solid #d5dbe5;
        background: #f8f9fc;
        color: var(--text-dark);
        padding: .5rem .9rem;
        font-weight: 600;
        transition: all .2s ease;
    }

    .filter-pill.active {
        background: linear-gradient(135deg, var(--brand-red), var(--brand-red-dark));
        border-color: transparent;
        color: #fff;
        box-shadow: 0 10px 20px rgba(215, 25, 33, 0.18);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 1rem;
        padding: .25rem 0 1rem;
    }

    .section-header h2 {
        margin: 0;
        font-weight: 800;
        letter-spacing: -.02em;
        color: var(--text-dark);
    }

    .eyebrow-sub {
        color: var(--brand-red);
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.7rem;
    }

    .product-card {
        border-radius: 20px;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: #fff;
    }

    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.10) !important;
    }

    .product-tag {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 2;
        background: var(--brand-red);
        color: #fff;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        padding: .52rem .7rem;
        box-shadow: 0 8px 18px rgba(215, 25, 33, 0.2);
    }

    .product-thumb {
        display: block;
        background: linear-gradient(180deg, #ffffff 0%, #f5f7fb 100%);
        border-bottom: 1px solid var(--line);
        padding: 1.05rem 1rem .5rem;
    }

    .product-thumb img {
        height: 190px;
        object-fit: contain;
        transition: transform .25s ease;
    }

    .product-card:hover .product-thumb img {
        transform: scale(1.04);
    }

    .brand-badge {
        display: inline-flex;
        align-items: center;
        padding: .38rem .7rem;
        border-radius: 999px;
        background: rgba(215, 25, 33, 0.08);
        color: var(--brand-red);
        font-weight: 700;
        font-size: .72rem;
        border: 1px solid rgba(215, 25, 33, 0.12);
    }

    .capacity-chip {
        display: inline-flex;
        align-items: center;
        padding: .34rem .55rem;
        border-radius: 999px;
        background: var(--brand-red-soft);
        color: var(--brand-red);
        border: 1px solid rgba(215, 25, 33, 0.12);
        font-size: .68rem;
        font-weight: 700;
        margin: .12rem .2rem .12rem 0;
    }

    .product-meta {
        border-top: 1px solid var(--line);
        padding-top: .9rem;
        margin-top: .9rem;
    }

    .price-text {
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: -.02em;
        color: var(--brand-red);
    }

    .view-btn {
        width: 100%;
        border-radius: 12px;
        padding: .7rem .8rem;
        font-weight: 700;
    }

    .chat-float-button {
        box-shadow: 0 15px 30px rgba(215, 25, 33, 0.28) !important;
    }
</style>

<div class="shop-shell container my-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="shop-banner mb-4">
        <div class="shop-banner-content">
            <span class="eyebrow">Khuyến mại mùa hè</span>
            <h1 class="display-6 fw-bold text-white mt-3 mb-2">RỰC RỠ HÈ - ĐIỀU HÒA GIÁ SỐC</h1>
            <p class="lead my-2 fs-6">Miễn phí 100% công lắp đặt và tặng bộ vật tư ống đồng trị giá lên đến 1.000.000đ khi mua điều hòa Casper, Daikin, Panasonic.</p>
        </div>
    </div>

    <div class="shop-filter p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-bold small me-1 text-dark"><i class="fa-solid fa-filter text-hc me-1"></i>Hãng sản xuất:</span>
            <a href="{{ route('shop.index') }}" class="filter-pill {{ !request('brand') ? 'active' : '' }}">Tất cả</a>
            @foreach($brands as $brand)
                <a href="{{ route('shop.index', ['brand' => $brand]) }}" class="filter-pill {{ request('brand') == $brand ? 'active' : '' }}">{{ $brand }}</a>
            @endforeach
        </div>

        <div class="small text-muted">
            Hiển thị <strong class="text-dark">{{ isset($airConditioners) ? $airConditioners->count() : 0 }}</strong> sản phẩm
        </div>
    </div>

    @php
        $chatRoute = auth()->check()
            ? (auth()->user()->role === 'admin' ? route('admin.chat.index') : (auth()->user()->role === 'staff' ? route('air_conditioners.index') : route('chat.index')))
            : route('login');
    @endphp

    <a href="{{ $chatRoute }}" class="btn btn-danger rounded-circle shadow-lg d-flex align-items-center justify-content-center text-white position-fixed chat-float-button" title="Chat với shop" aria-label="Chat với shop" style="width: 62px; height: 62px; right: 24px; bottom: 26px; z-index: 1100; font-size: 1.4rem;">
        <i class="fa-solid fa-comments"></i>
    </a>

    <div class="section-header mb-3">
        <div>
            <div class="eyebrow-sub">Bộ sưu tập</div>
            <h2>Điều hòa thông minh cho mọi không gian</h2>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @if(isset($airConditioners) && $airConditioners->count() > 0)
            @foreach($airConditioners as $item)
                @php
                    $firstVariant = $item->variants->first();
                    $variantList = $item->variants ?? collect();
                    $displayPrice = $firstVariant ? (float) $firstVariant->price : (float) $item->price;
                    $displayCapacity = $firstVariant ? $firstVariant->capacity_name : 'Thông tin BTU';
                @endphp
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm product-card position-relative overflow-hidden">
                        <span class="product-tag">Giá tốt</span>

                        <a href="{{ route('shop.detail', $item->id) }}" class="product-thumb text-center">
                            @php $productImage = $item->primary_image_path ?? $item->image; @endphp
                            @if($productImage)
                                <img src="{{ request()->getBaseUrl() . '/storage/' . $productImage }}" alt="{{ $item->name }}">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted rounded" style="height: 190px;">
                                    <i class="fa-regular fa-image me-2"></i>Không có ảnh
                                </div>
                            @endif
                        </a>

                        <div class="card-body d-flex flex-column p-3">
                            <div class="mb-2">
                                <span class="brand-badge">{{ $item->brand }}</span>
                            </div>

                            <h6 class="card-title fw-bold text-truncate-2 mb-2">
                                <a href="{{ route('shop.detail', $item->id) }}" class="text-dark text-decoration-none">
                                    {{ $item->name }}
                                </a>
                            </h6>

                            <div class="mb-2" style="min-height: 28px;">
                                @if($variantList->count() > 0)
                                    @foreach($variantList->take(3) as $variant)
                                        <span class="capacity-chip">{{ $variant->capacity_name ?? 'BTU' }}</span>
                                    @endforeach
                                    @if($variantList->count() > 3)
                                        <span class="capacity-chip">+{{ $variantList->count() - 3 }}</span>
                                    @endif
                                @else
                                    <span class="capacity-chip">{{ $displayCapacity }}</span>
                                @endif
                            </div>

                            <div class="product-meta mt-auto">
                                <div class="d-flex align-items-baseline justify-content-between gap-2">
                                    <span class="price-text">{{ number_format($displayPrice, 0, ',', '.') }} đ</span>
                                </div>

                                <a href="{{ route('shop.detail', $item->id) }}" class="btn btn-hc view-btn mt-3 fw-semibold">
                                    <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12 text-center py-5 bg-white rounded shadow-sm border">
                <i class="fa-solid fa-box-open display-1 text-muted mb-3"></i>
                <p class="text-muted fs-5 mb-2">Không tìm thấy sản phẩm phù hợp.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-sm btn-hc mt-2">
                    <i class="fa-solid fa-rotate-left me-1"></i>Xem tất cả sản phẩm
                </a>
            </div>
        @endif
    </div>
</div>
@endsection