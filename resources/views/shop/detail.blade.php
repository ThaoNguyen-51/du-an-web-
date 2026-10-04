@extends('layouts.shop')

@section('content')
@php
    $firstVariant = $airConditioner->variants->first();
    $rawSpecs = $firstVariant?->specifications;
    $specs = is_array($rawSpecs) ? $rawSpecs : (is_string($rawSpecs) ? json_decode($rawSpecs, true) ?? [] : []);

    $pickSpec = function (array $keys, $fallback = null) use ($specs) {
        foreach ($keys as $key) {
            if (isset($specs[$key]) && $specs[$key] !== null && $specs[$key] !== '') {
                return $specs[$key];
            }
        }

        return $fallback;
    };

    $displayOrigin = $pickSpec(['origin'], $airConditioner->origin ?? 'Thái Lan');
    $displayWarranty = $pickSpec(['warranty'], $airConditioner->warranty ?? '3 năm');
    $displayCapacity = $pickSpec(['cooling_capacity', 'capacity_name'], $firstVariant?->capacity_name ?? '9000 BTU');
    $displayRoomSize = $pickSpec(['effective_area', 'room_size'], $airConditioner->room_size ?? 'Dưới 15 m2');
    $displayInverter = $pickSpec(['inverter_type', 'inverter'], $airConditioner->inverter_type ?? 'Không Inverter');
    $displayType = $pickSpec(['machine_type', 'type'], $airConditioner->type ?? '1 chiều');
    $displayPower = $pickSpec(['power_consumption'], $airConditioner->power_consumption ?? '833 W');
    $displayEnergy = $pickSpec(['energy_stars', 'energy_rating', 'cspf'], $airConditioner->energy_rating ?? '1 sao / CSPF: 3.29');
    $displayAntibacterial = $pickSpec(['antibacterial_feature', 'self_clean', 'self_cleaning'], $airConditioner->antibacterial_feature ?? 'Tự làm sạch ECO CLEAN');
    $displayCooling = $pickSpec(['cooling_feature', 'turbo_mode', 'turbo'], $airConditioner->cooling_feature ?? 'Làm lạnh nhanh Turbo');

    $specRows = [
        ['Loại máy', $displayType],
        ['Công nghệ Inverter', $displayInverter],
        ['Công suất làm lạnh', $displayCapacity],
        ['Phạm vi làm lạnh hiệu quả', $displayRoomSize],
        ['Tiêu thụ điện', $displayPower],
        ['Tiết kiệm điện', $displayEnergy],
        ['Hiệu suất năng lượng (CSPF)', $pickSpec(['cspf'], '—')],
        ['Model Dàn Lạnh', $pickSpec(['indoor_model'], '—')],
        ['Kích thước Dàn Lạnh', $pickSpec(['indoor_dimensions'], '—')],
        ['Trọng lượng Dàn Lạnh', $pickSpec(['indoor_weight'], '—')],
        ['Độ ồn Dàn Lạnh', $pickSpec(['indoor_noise'], '—')],
        ['Model Dàn Nóng', $pickSpec(['outdoor_model'], '—')],
        ['Kích thước Dàn Nóng', $pickSpec(['outdoor_dimensions'], '—')],
        ['Trọng lượng Dàn Nóng', $pickSpec(['outdoor_weight'], '—')],
        ['Độ ồn Dàn Nóng', $pickSpec(['outdoor_noise'], '—')],
        ['Môi chất lạnh (Gas)', $pickSpec(['refrigerant'], '—')],
        ['Chiều dài ống tối đa', $pickSpec(['max_pipe_length'], '—')],
        ['Chênh lệch độ cao tối đa', $pickSpec(['max_elevation_diff'], '—')],
    ];

    $variantData = $airConditioner->variants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'capacity_name' => $variant->capacity_name,
            'price' => (float) $variant->price,
            'specifications' => is_array($variant->specifications) ? $variant->specifications : [],
        ];
    })->values();
@endphp
<style>
    .bg-hc { background-color: #d71921 !important; }
    .text-hc { color: #d71921 !important; }
    .btn-hc { background-color: #d71921; color: #fff; border: none; }
    .btn-hc:hover { background-color: #b51219; color: #fff; }
    .btn-buy-now {
        background: #ffb000;
        color: #1f2937;
        border: 1px solid #e5a000;
    }
    .btn-buy-now:hover {
        background: #f0a400;
        color: #111827;
    }
    .cursor-pointer { cursor: pointer; }
    
    /* Style custom cho biến thể BTU */
    .variant-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        transition: all 0.2s ease-in-out;
        background-color: #fff;
    }
    .btn-check:checked + .variant-card {
        border-color: #d71921;
        background-color: #fff5f5;
    }
    .btn-check:checked + .variant-card .variant-name {
        color: #d71921;
    }
    .btn-check:checked + .variant-card .check-icon {
        display: inline-block !important;
    }
    .similar-product-card {
        border-radius: 16px;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .similar-product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 28px rgba(15, 23, 42, .1) !important;
    }
    .similar-product-image {
        height: 150px;
        object-fit: contain;
        background: linear-gradient(180deg, #fff, #f7f8fb);
    }
    .wishlist-toggle.active {
        background: #d71921;
        border-color: #d71921;
        color: #fff;
    }
</style>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb bg-light p-2 rounded small border mb-0">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.index') }}" class="text-decoration-none text-muted">Sản phẩm</a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $airConditioner->name }}</li>
        </ol>
    </nav>

    <!-- Thông báo Alert -->
    <!-- KHỐI 1: THÔNG TIN MUA HÀNG CHÍNH -->
    <div class="card border-0 shadow-sm p-4 mb-4">
        <form action="{{ route('cart.add', $airConditioner->id) }}" method="POST" data-ajax-toast>
            @csrf
            <div class="row g-4">
                <!-- Cột trái: Ảnh Sản Phẩm -->
                <div class="col-lg-5 text-center">
                    <div class="border rounded p-3 mb-3 bg-white d-flex align-items-center justify-content-center position-relative" style="min-height: 350px;">
                        @php
                            $galleryImages = $airConditioner->images->pluck('image_path')->all();
                            if (empty($galleryImages) && $airConditioner->image) {
                                $galleryImages = [$airConditioner->image];
                            }
                            $galleryImageUrls = array_map(fn ($imagePath) => request()->getBaseUrl() . '/storage/' . $imagePath, $galleryImages);
                            $displayMainImage = $galleryImages[0] ?? null;
                        @endphp
                        @if($displayMainImage)
                            <img id="main-product-img" src="{{ request()->getBaseUrl() . '/storage/' . $displayMainImage }}" class="img-fluid" style="max-height: 300px; object-fit: contain;" alt="{{ $airConditioner->name }}">
                            @if(count($galleryImageUrls) > 1)
                                <button type="button" class="btn btn-light border shadow-sm gallery-nav-btn" data-direction="-1" aria-label="Ảnh trước" title="Ảnh trước" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); z-index:2; width:40px; height:40px;">&lt;</button>
                                <button type="button" class="btn btn-light border shadow-sm gallery-nav-btn" data-direction="1" aria-label="Ảnh tiếp theo" title="Ảnh tiếp theo" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); z-index:2; width:40px; height:40px;">&gt;</button>
                            @endif
                        @else
                            <div class="text-muted py-5">
                                <i class="fa-regular fa-image display-1 d-block mb-2 text-secondary"></i>
                                <span>Chưa có hình ảnh</span>
                            </div>
                        @endif
                    </div>

                    @if(!empty($galleryImages))
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            @foreach($galleryImages as $galleryImage)
                                <button type="button" class="btn btn-light border p-1 thumbnail-btn" data-index="{{ $loop->index }}">
                                    <img src="{{ request()->getBaseUrl() . '/storage/' . $galleryImage }}" alt="Ảnh sản phẩm" style="width: 62px; height: 62px; object-fit: cover; border-radius: 8px;">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Cột phải: Đặt hàng & Biến thể -->
                <div class="col-lg-7">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <h3 class="fw-bold text-dark mb-2">{{ $airConditioner->name }}</h3>
                        @auth
                            @if(auth()->user()->role === 'user')
                                <button type="button"
                                    class="btn btn-outline-danger rounded-circle wishlist-toggle"
                                    data-wishlist-url="{{ route('wishlist.toggle', $airConditioner) }}"
                                    title="Lưu vào yêu thích"
                                    aria-label="Lưu vào yêu thích">
                                    <i class="{{ $isWishlisted ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                                </button>
                            @endif
                        @endauth
                    </div>
                    <p class="text-muted small mb-3">
                        Thương hiệu: <span class="badge bg-secondary px-2 py-1">{{ $airConditioner->brand }}</span>
                    </p>

                    <!-- Hiển thị giá -->
                    <div class="bg-light p-3 rounded mb-3 d-flex align-items-baseline gap-3 border">
                        <span id="display-price" class="fs-2 fw-bold text-hc">
                            {{ number_format($airConditioner->price, 0, ',', '.') }} đ
                        </span>
                    </div>

                    <!-- Chọn công suất BTU -->
                    @if($airConditioner->variants && $airConditioner->variants->count() > 0)
                    <div class="mb-4">
                        <label class="fw-bold small mb-2 text-dark">Chọn công suất BTU:</label>
                        <div class="row g-2">
                            @foreach($airConditioner->variants as $key => $variant)
                                <div class="col-6 col-md-4">
                                    <label class="w-100 cursor-pointer">
                                        <input type="radio" name="variant_id" value="{{ $variant->id }}" 
                                               data-price="{{ number_format($variant->price, 0, ',', '.') }} đ"
                                               data-capacity="{{ $variant->capacity_name }}"
                                               class="btn-check variant-option" {{ $key == 0 ? 'checked' : '' }}>
                                        <div class="variant-card position-relative">
                                            <div class="fw-bold small text-truncate variant-name">
                                                <i class="fa-solid fa-check circle text-hc me-1 check-icon d-none"></i>
                                                {{ $variant->capacity_name }}
                                            </div>
                                            <div class="small fw-semibold text-muted">
                                                {{ number_format($variant->price, 0, ',', '.') }} đ
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Bộ tăng giảm số lượng -->
                    <div class="mb-4 d-flex align-items-center gap-3">
                        <label class="fw-bold small">Số lượng:</label>
                        <div class="input-group" style="width: 130px;">
                            <button class="btn btn-outline-secondary" type="button" id="btn-decrease">-</button>
                            <input type="number" name="quantity" id="quantity-input" value="1" min="1" class="form-control text-center fw-bold">
                            <button class="btn btn-outline-secondary" type="button" id="btn-increase">+</button>
                        </div>
                    </div>

                    <!-- Nút Mua Hàng -->
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-hc btn-lg w-100 py-3 fw-bold text-uppercase shadow-sm">
                                <i class="fa-solid fa-cart-plus me-2"></i> THÊM VÀO GIỎ HÀNG
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" name="buy_now" value="1" class="btn btn-buy-now btn-lg w-100 py-3 fw-bold text-uppercase shadow-sm">
                                <i class="fa-solid fa-bolt me-2"></i> MUA NGAY
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- KHỐI 2: MÔ TẢ & THÔNG SỐ KỸ THUẬT -->
    <div class="row g-4">
        <!-- Bài viết Mô tả -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="fa-solid fa-circle-info me-2 text-hc"></i>Đánh giá chi tiết {{ $airConditioner->name }}
                </h5>
                <div class="product-description lh-lg text-secondary">
                    {!! nl2br(e($airConditioner->description)) !!}
                </div>
            </div>
        </div>

        <!-- Thông số kỹ thuật -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light fw-bold py-3 text-dark border-bottom">
                    <i class="fa-solid fa-sliders me-2 text-hc"></i>Thông số kỹ thuật
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover table-sm small align-middle mb-0">
                        <tbody id="tech-spec-body">
                            <tr>
                                <td class="text-muted ps-3 py-2" style="width: 45%;">Xuất xứ</td>
                                <td class="fw-semibold text-dark py-2">{{ $displayOrigin }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-3 py-2">Bảo hành</td>
                                <td class="fw-semibold text-dark py-2">{{ $displayWarranty }}</td>
                            </tr>
                            @foreach($specRows as [$label, $value])
                                <tr>
                                    <td class="text-muted ps-3 py-2">{{ $label }}</td>
                                    <td class="fw-semibold text-dark py-2">{{ $value ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <section class="card border-0 shadow-sm mt-4" id="product-reviews">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-star text-warning me-2"></i>Đánh giá sản phẩm</h5>
            <span class="text-muted small">{{ $airConditioner->reviews->count() }} đánh giá</span>
        </div>
        <div class="card-body">
            @forelse($airConditioner->reviews as $review)
                <article class="border-bottom pb-3 mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <strong>{{ $review->user->name }}</strong>
                        <time class="small text-muted" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d/m/Y') }}</time>
                    </div>
                    <div class="text-warning mb-2" aria-label="{{ $review->rating }} trên 5 sao">
                        @for($star = 1; $star <= 5; $star++)
                            <i class="fa-{{ $star <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                        @endfor
                    </div>
                    <p class="mb-2 text-break">{{ $review->comment }}</p>
                    @if(!empty($review->images))
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            @foreach($review->images as $reviewImage)
                                <a href="{{ asset('storage/'.$reviewImage) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('storage/'.$reviewImage) }}" alt="Ảnh đánh giá" style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
                                </a>
                            @endforeach
                        </div>
                    @endif
                    @if($review->admin_reply)
                        <div class="bg-light border-start border-3 border-danger rounded p-3">
                            <strong class="small text-danger">Phản hồi từ cửa hàng</strong>
                            <p class="mb-0 mt-1 text-break">{{ $review->admin_reply }}</p>
                        </div>
                    @endif
                </article>
            @empty
                <p class="text-muted mb-0">Sản phẩm chưa có đánh giá.</p>
            @endforelse

            @if(auth()->user()->role === 'user' && $eligibleReviewOrders->isNotEmpty())
                <form action="{{ route('product-reviews.store', $airConditioner) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <h6 class="fw-bold mb-3">Viết đánh giá</h6>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="review-order" class="form-label">Đơn hàng đã hoàn thành</label>
                            <select id="review-order" name="order_id" class="form-select" required>
                                @foreach($eligibleReviewOrders as $reviewOrder)
                                    <option value="{{ $reviewOrder->id }}">Đơn #{{ $reviewOrder->id }} · {{ $reviewOrder->created_at->format('d/m/Y') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="review-rating" class="form-label">Số sao</label>
                            <select id="review-rating" name="rating" class="form-select" required>
                                <option value="5">5 sao</option>
                                <option value="4">4 sao</option>
                                <option value="3">3 sao</option>
                                <option value="2">2 sao</option>
                                <option value="1">1 sao</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="review-comment" class="form-label">Nhận xét</label>
                            <textarea id="review-comment" name="comment" class="form-control" rows="4" minlength="5" maxlength="2000" required>{{ old('comment') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="review-images" class="form-label">Hình ảnh (tối đa 5 ảnh)</label>
                            <input id="review-images" type="file" name="images[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                            <div class="form-text">Mỗi ảnh tối đa 3MB.</div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-hc"><i class="fa-solid fa-paper-plane me-2"></i>Gửi đánh giá</button>
                        </div>
                    </div>
                </form>
            @elseif(auth()->user()->role === 'user' && $incompleteReviewOrders->isNotEmpty())
                <div class="alert alert-info border-0 mt-4 mb-0">
                    <strong>Bạn có đơn hàng sản phẩm này đang được xử lý.</strong>
                    <span> Bạn có thể gửi đánh giá sau khi đơn chuyển sang trạng thái Đã hoàn thành.</span>
                    <ul class="small mb-0 mt-2">
                        @foreach($incompleteReviewOrders as $reviewOrder)
                            <li>Đơn #{{ $reviewOrder->id }}: {{ $reviewOrder->status_label }}</li>
                        @endforeach
                    </ul>
                </div>
            @elseif(auth()->user()->role === 'user')
                <p class="small text-muted border-top pt-3 mt-3 mb-0">Chỉ khách hàng có đơn hàng đã hoàn thành mới được gửi đánh giá. Bạn vẫn có thể đọc các đánh giá bên trên.</p>
            @endif
        </div>
    </section>

    @if($recommendedProducts->isNotEmpty())
        <section class="mt-5" aria-labelledby="similar-products-title">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                <div>
                    <div class="small text-danger fw-bold text-uppercase">Có thể bạn quan tâm</div>
                    <h2 id="similar-products-title" class="h4 fw-bold mb-0">Sản phẩm có thông số tương tự</h2>
                </div>
                <a href="{{ route('shop.index') }}" class="btn btn-sm btn-outline-danger">Xem thêm sản phẩm</a>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                @foreach($recommendedProducts as $recommended)
                    @php
                        $recommendedVariant = $recommended->variants->first();
                        $recommendedSpecs = is_array($recommendedVariant?->specifications) ? $recommendedVariant->specifications : [];
                        $recommendedImage = $recommended->primary_image_path ?? $recommended->image;
                    @endphp
                    <div class="col">
                        <article class="card h-100 border-0 shadow-sm overflow-hidden similar-product-card">
                            <a href="{{ route('shop.detail', $recommended->id) }}" class="text-decoration-none">
                                @if($recommendedImage)
                                    <img src="{{ request()->getBaseUrl() . '/storage/' . $recommendedImage }}" class="card-img-top similar-product-image" alt="{{ $recommended->name }}">
                                @else
                                    <div class="similar-product-image d-flex align-items-center justify-content-center text-muted"><i class="fa-regular fa-image fa-2x"></i></div>
                                @endif
                            </a>
                            <div class="card-body d-flex flex-column p-3">
                                <span class="badge rounded-pill text-bg-light border text-danger align-self-start mb-2">{{ $recommended->brand ?: 'Điều hòa' }}</span>
                                <h3 class="h6 fw-bold mb-2">
                                    <a href="{{ route('shop.detail', $recommended->id) }}" class="text-dark text-decoration-none">{{ $recommended->name }}</a>
                                </h3>
                                <div class="small text-muted mb-2">
                                    <div><i class="fa-solid fa-bolt text-danger me-1"></i>{{ $recommendedVariant?->capacity_name ?? 'Chưa cập nhật công suất' }}</div>
                                    @if(!empty($recommendedSpecs['machine_type']))
                                        <div><i class="fa-solid fa-snowflake text-danger me-1"></i>{{ $recommendedSpecs['machine_type'] }}</div>
                                    @endif
                                </div>
                                <div class="mt-auto">
                                    <div class="fw-bold text-danger mb-2">{{ number_format((float) ($recommendedVariant?->price ?? $recommended->price), 0, ',', '.') }} đ</div>
                                    <a href="{{ route('shop.detail', $recommended->id) }}" class="btn btn-outline-danger btn-sm w-100">Xem chi tiết</a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('.variant-option');
        const priceDisplay = document.getElementById('display-price');
        const qtyInput = document.getElementById('quantity-input');
        const btnDecrease = document.getElementById('btn-decrease');
        const btnIncrease = document.getElementById('btn-increase');
        const techTableBody = document.getElementById('tech-spec-body');
        const mainProductImg = document.getElementById('main-product-img');
        const galleryButtons = document.querySelectorAll('.thumbnail-btn');
        const galleryNavButtons = document.querySelectorAll('.gallery-nav-btn');
        const galleryImageUrls = @json($galleryImageUrls);
        let currentGalleryIndex = 0;
        const variantData = @json($variantData);

        const specLabelMap = [
            ['machine_type', 'Loại máy'],
            ['inverter', 'Công nghệ Inverter'],
            ['inverter_type', 'Công nghệ Inverter'],
            ['cooling_capacity', 'Công suất làm lạnh'],
            ['capacity_name', 'Công suất làm lạnh'],
            ['effective_area', 'Phạm vi làm lạnh hiệu quả'],
            ['room_size', 'Phạm vi làm lạnh hiệu quả'],
            ['power_consumption', 'Tiêu thụ điện'],
            ['energy_stars', 'Tiết kiệm điện'],
            ['energy_rating', 'Tiết kiệm điện'],
            ['cspf', 'Hiệu suất năng lượng (CSPF)'],
            ['indoor_model', 'Model Dàn Lạnh'],
            ['indoor_dimensions', 'Kích thước Dàn Lạnh'],
            ['indoor_weight', 'Trọng lượng Dàn Lạnh'],
            ['indoor_noise', 'Độ ồn Dàn Lạnh'],
            ['outdoor_model', 'Model Dàn Nóng'],
            ['outdoor_dimensions', 'Kích thước Dàn Nóng'],
            ['outdoor_weight', 'Trọng lượng Dàn Nóng'],
            ['outdoor_noise', 'Độ ồn Dàn Nóng'],
            ['refrigerant', 'Môi chất lạnh (Gas)'],
            ['max_pipe_length', 'Chiều dài ống tối đa'],
            ['max_elevation_diff', 'Chênh lệch độ cao tối đa'],
        ];

        function formatValue(value) {
            return value === null || value === undefined || value === '' ? '—' : value;
        }

        function renderSpecTable(variant) {
            if (!techTableBody || !variant) return;

            const specs = variant.specifications || {};
            const rows = [
                ['Loại máy', specs.machine_type || specs.type || '—'],
                ['Công nghệ Inverter', specs.inverter || specs.inverter_type || '—'],
                ['Công suất làm lạnh', specs.cooling_capacity || variant.capacity_name || '—'],
                ['Phạm vi làm lạnh hiệu quả', specs.effective_area || specs.room_size || '—'],
                ['Tiêu thụ điện', specs.power_consumption || '—'],
                ['Tiết kiệm điện', specs.energy_stars || specs.energy_rating || '—'],
                ['Hiệu suất năng lượng (CSPF)', specs.cspf || '—'],
                ['Model Dàn Lạnh', specs.indoor_model || '—'],
                ['Kích thước Dàn Lạnh', specs.indoor_dimensions || '—'],
                ['Trọng lượng Dàn Lạnh', specs.indoor_weight || '—'],
                ['Độ ồn Dàn Lạnh', specs.indoor_noise || '—'],
                ['Model Dàn Nóng', specs.outdoor_model || '—'],
                ['Kích thước Dàn Nóng', specs.outdoor_dimensions || '—'],
                ['Trọng lượng Dàn Nóng', specs.outdoor_weight || '—'],
                ['Độ ồn Dàn Nóng', specs.outdoor_noise || '—'],
                ['Môi chất lạnh (Gas)', specs.refrigerant || '—'],
                ['Chiều dài ống tối đa', specs.max_pipe_length || '—'],
                ['Chênh lệch độ cao tối đa', specs.max_elevation_diff || '—'],
            ];

            techTableBody.innerHTML = rows.map(([label, value]) => `
                <tr>
                    <td class="text-muted ps-3 py-2">${label}</td>
                    <td class="fw-semibold text-dark py-2">${formatValue(value)}</td>
                </tr>
            `).join('');
        }

        function updateVariantInfo(radioElem) {
            if (!radioElem) return;

            const selected = variantData.find(item => String(item.id) === String(radioElem.value));
            if (!selected) return;

            const price = Number(selected.price || 0);
            if (priceDisplay) {
                priceDisplay.innerText = new Intl.NumberFormat('vi-VN').format(price) + ' đ';
            }

            renderSpecTable(selected);
        }

        const checkedRadio = document.querySelector('.variant-option:checked');
        updateVariantInfo(checkedRadio);

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) updateVariantInfo(this);
            });
        });

        if (btnDecrease && btnIncrease && qtyInput) {
            btnDecrease.addEventListener('click', function() {
                let currentVal = parseInt(qtyInput.value) || 1;
                if (currentVal > 1) qtyInput.value = currentVal - 1;
            });

            btnIncrease.addEventListener('click', function() {
                let currentVal = parseInt(qtyInput.value) || 1;
                qtyInput.value = currentVal + 1;
            });
        }

        function showGalleryImage(index) {
            if (!mainProductImg || galleryImageUrls.length < 2) return;
            currentGalleryIndex = (index + galleryImageUrls.length) % galleryImageUrls.length;
            mainProductImg.src = galleryImageUrls[currentGalleryIndex];
        }

        galleryButtons.forEach((button) => {
            button.addEventListener('click', function () {
                showGalleryImage(Number(this.dataset.index));
            });
        });

        galleryNavButtons.forEach((button) => {
            button.addEventListener('click', function () {
                showGalleryImage(currentGalleryIndex + Number(this.dataset.direction));
            });
        });

        const wishlistButton = document.querySelector('.wishlist-toggle');
        if (wishlistButton) {
            wishlistButton.addEventListener('click', function () {
                const token = document.querySelector('input[name="_token"]')?.value;
                wishlistButton.disabled = true;
                fetch(wishlistButton.dataset.wishlistUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                }).then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Không thể cập nhật yêu thích.');
                    const icon = wishlistButton.querySelector('i');
                    icon.classList.toggle('fa-solid', data.wishlisted);
                    icon.classList.toggle('fa-regular', !data.wishlisted);
                    wishlistButton.classList.toggle('active', data.wishlisted);
                    wishlistButton.setAttribute('title', data.wishlisted ? 'Bỏ yêu thích' : 'Lưu vào yêu thích');
                    wishlistButton.setAttribute('aria-label', data.wishlisted ? 'Bỏ yêu thích' : 'Lưu vào yêu thích');
                    if (window.shopShowToast) window.shopShowToast(data.message);
                })
                  .catch(() => {
                      wishlistButton.disabled = false;
                      if (window.shopShowToast) window.shopShowToast('Không thể cập nhật danh sách yêu thích. Vui lòng thử lại.', 'error');
                  })
                  .finally(() => {
                      wishlistButton.disabled = false;
                  });
            });
        }
    });
</script>
@endsection