@extends('layouts.shop')

@section('content')
@php
    $firstVariant = $airConditioner->variants->first();
    $defaultSpecs = is_array($firstVariant?->specifications) ? $firstVariant->specifications : [];
    $pick = function (array $keys, $fallback = '—') use ($defaultSpecs) {
        foreach ($keys as $key) {
            if (!empty($defaultSpecs[$key])) {
                return $defaultSpecs[$key];
            }
        }
        return $fallback;
    };

    $variantData = $airConditioner->variants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'capacity_name' => $variant->capacity_name,
            'price' => (float) $variant->price,
            'specifications' => is_array($variant->specifications) ? $variant->specifications : [],
        ];
    })->values();

    $galleryImages = $airConditioner->images->pluck('image_path')->all();
    if (empty($galleryImages) && $airConditioner->image) {
        $galleryImages = [$airConditioner->image];
    }
    $galleryImageUrls = array_map(fn ($imagePath) => filter_var($imagePath, FILTER_VALIDATE_URL) ? $imagePath : request()->getBaseUrl() . '/storage/' . $imagePath, $galleryImages);
@endphp

<style>
    :root {
        --brand-red: #d71921;
        --brand-red-dark: #b51219;
        --surface-soft: #f8f9fc;
        --line: #e8edf5;
        --text-dark: #1f2937;
        --text-muted: #667085;
    }

    .admin-detail-shell {
        padding-bottom: 2rem;
    }

    .admin-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
        border: 1px solid var(--line);
        border-radius: 22px;
        padding: 1.2rem 1.4rem;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
    }

    .admin-title {
        font-weight: 800;
        letter-spacing: -.02em;
        color: var(--text-dark);
    }

    .admin-panel {
        border: 1px solid var(--line);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .admin-hero {
        background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
    }

    .product-image-box {
        border: 1px solid var(--line);
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f7f9fc 100%);
        min-height: 290px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-card {
        background: var(--surface-soft);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 1rem;
        height: 100%;
    }

    .info-label {
        display: block;
        color: var(--text-muted);
        font-size: .76rem;
        margin-bottom: .35rem;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .info-value {
        font-weight: 800;
        color: var(--text-dark);
        font-size: 1.05rem;
    }

    .variant-card {
        border: 1px solid var(--line);
        border-radius: 18px;
        background: linear-gradient(180deg, #fff 0%, #fafbff 100%);
        padding: 1.1rem;
        height: 100%;
        cursor: pointer;
        transition: all .2s ease;
    }

    .variant-card.is-selected {
        border-color: var(--brand-red);
        background: linear-gradient(180deg, #fff5f5 0%, #ffffff 100%);
        box-shadow: 0 10px 24px rgba(215, 25, 33, 0.08);
    }

    .variant-card table td {
        padding: .5rem 0;
        vertical-align: top;
    }

    .spec-table td {
        padding: .8rem 1rem;
        border-bottom: 1px solid var(--line);
    }

    .spec-table tr:last-child td {
        border-bottom: none;
    }
</style>

<div class="admin-detail-shell container my-4">
    <div class="admin-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-bold text-danger mb-2">Quản lý sản phẩm</div>
            <h3 class="admin-title mb-1"><i class="fa-solid fa-boxes-stacked me-2 text-danger"></i>Chi tiết sản phẩm</h3>
            <p class="text-muted small mb-0">Trang xem thông tin nội bộ dành cho quản trị và nhân sự</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('air_conditioners.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
            <a href="{{ route('air_conditioners.edit', $airConditioner->id) }}" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark">
                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa
            </a>
        </div>
    </div>

    <div class="admin-panel admin-hero p-4 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-4">
                <div class="product-image-box p-3 position-relative">
                    @php $displayMainImage = $galleryImages[0] ?? null; @endphp
                    @if($displayMainImage)
                        <img id="admin-main-product-img" src="{{ request()->getBaseUrl() . '/storage/' . $displayMainImage }}" class="img-fluid" style="max-height: 230px; object-fit: contain;" alt="{{ $airConditioner->name }}">
                        @if(count($galleryImageUrls) > 1)
                            <button type="button" class="btn btn-light border shadow-sm admin-gallery-nav" data-direction="-1" aria-label="Ảnh trước" title="Ảnh trước" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); z-index:2; width:40px; height:40px;"><i class="fa-solid fa-chevron-left"></i></button>
                            <button type="button" class="btn btn-light border shadow-sm admin-gallery-nav" data-direction="1" aria-label="Ảnh tiếp theo" title="Ảnh tiếp theo" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); z-index:2; width:40px; height:40px;"><i class="fa-solid fa-chevron-right"></i></button>
                        @endif
                    @else
                        <div class="text-muted text-center py-5">
                            <i class="fa-regular fa-image fs-1 d-block mb-2"></i>
                            <span>Không có hình ảnh</span>
                        </div>
                    @endif
                </div>
                @if(count($galleryImageUrls) > 1)
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                        @foreach($galleryImageUrls as $imageIndex => $imageUrl)
                            <button type="button" class="btn btn-light border p-1 admin-gallery-thumbnail" data-index="{{ $imageIndex }}" aria-label="Xem ảnh {{ $imageIndex + 1 }}">
                                <img src="{{ $imageUrl }}" alt="Ảnh {{ $imageIndex + 1 }} của {{ $airConditioner->name }}" style="width: 54px; height: 54px; object-fit: cover; border-radius: 4px;">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-8">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <div class="small text-uppercase fw-bold text-danger mb-1">{{ $airConditioner->brand }}</div>
                        <h4 class="fw-bold text-dark mb-0">{{ $airConditioner->name }}</h4>
                    </div>
                    <span class="badge bg-dark rounded-pill px-3 py-2">HC-{{ str_pad($airConditioner->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="info-card">
                            <span class="info-label">Giá niêm yết</span>
                            <div class="info-value text-danger">{{ number_format((float) $airConditioner->price, 0, ',', '.') }}đ</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <span class="info-label">Bảo hành</span>
                            <div class="info-value">{{ $airConditioner->warranty ?? '3 năm' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <span class="info-label">Xuất xứ</span>
                            <div class="info-value">{{ $airConditioner->origin ?? 'Thái Lan' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <span class="info-label">Trạng thái</span>
                            <div class="info-value">Đang kinh doanh</div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <span class="info-label">Mô tả sản phẩm</span>
                    <div class="text-secondary lh-lg">
                        {!! nl2br(e($airConditioner->description ?? 'Chưa có mô tả cho sản phẩm này.')) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-panel mb-4">
        <div class="card-header bg-white fw-bold py-3 px-4 border-bottom text-danger">
            <i class="fa-solid fa-layer-group me-2"></i>Danh sách phiên bản công suất (BTU)
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                @forelse($airConditioner->variants as $variant)
                    @php
                        $variantSpecs = is_array($variant->specifications) ? $variant->specifications : [];
                    @endphp
                    <div class="col-lg-6">
                        <div class="variant-card variant-selectable {{ $loop->first ? 'is-selected' : '' }}" data-variant-id="{{ $variant->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark mb-0">{{ $variant->capacity_name }}</h6>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ number_format((float) $variant->price, 0, ',', '.') }}đ</span>
                            </div>
                            <div class="small text-muted mb-3">
                                <span class="me-3"><strong>Tồn kho:</strong> {{ $variant->stock ?? 0 }}</span>
                                <span><strong>Trọng lượng:</strong> {{ $variant->weight ?? 25000 }} g</span>
                            </div>
                            <table class="table table-sm table-borderless mb-0 small">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width: 48%;">Loại máy</td>
                                        <td class="fw-semibold text-dark">{{ $variantSpecs['machine_type'] ?? $variantSpecs['type'] ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Công nghệ Inverter</td>
                                        <td class="fw-semibold text-dark">{{ $variantSpecs['inverter'] ?? $variantSpecs['inverter_type'] ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Công suất làm lạnh</td>
                                        <td class="fw-semibold text-dark">{{ $variantSpecs['cooling_capacity'] ?? $variant->capacity_name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Phạm vi làm lạnh</td>
                                        <td class="fw-semibold text-dark">{{ $variantSpecs['effective_area'] ?? $variantSpecs['room_size'] ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">Chưa có biến thể cho sản phẩm này.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="admin-panel">
        <div class="card-header bg-white fw-bold py-3 px-4 border-bottom text-danger">
            <i class="fa-solid fa-sliders me-2"></i>Bảng thông số kỹ thuật chi tiết
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 spec-table">
                <tbody id="admin-spec-body">
                    @foreach([
                        ['Loại máy', $pick(['machine_type', 'type'])],
                        ['Công nghệ Inverter', $pick(['inverter', 'inverter_type'])],
                        ['Công suất làm lạnh', $pick(['cooling_capacity', 'capacity_name'])],
                        ['Phạm vi làm lạnh hiệu quả', $pick(['effective_area', 'room_size'])],
                        ['Tiêu thụ điện', $pick(['power_consumption'])],
                        ['Tiết kiệm điện', $pick(['energy_stars', 'energy_rating'])],
                        ['Hiệu suất năng lượng (CSPF)', $pick(['cspf'])],
                        ['Model Dàn Lạnh', $pick(['indoor_model'])],
                        ['Kích thước Dàn Lạnh', $pick(['indoor_dimensions'])],
                        ['Trọng lượng Dàn Lạnh', $pick(['indoor_weight'])],
                        ['Độ ồn Dàn Lạnh', $pick(['indoor_noise'])],
                        ['Model Dàn Nóng', $pick(['outdoor_model'])],
                        ['Kích thước Dàn Nóng', $pick(['outdoor_dimensions'])],
                        ['Trọng lượng Dàn Nóng', $pick(['outdoor_weight'])],
                        ['Độ ồn Dàn Nóng', $pick(['outdoor_noise'])],
                        ['Môi chất lạnh (Gas)', $pick(['refrigerant'])],
                        ['Chiều dài ống tối đa', $pick(['max_pipe_length'])],
                        ['Chênh lệch độ cao tối đa', $pick(['max_elevation_diff'])],
                    ] as [$label, $value])
                        <tr>
                            <td class="text-muted" style="width: 42%;">{{ $label }}</td>
                            <td class="fw-semibold text-dark">{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-panel mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center fw-bold py-3 px-4 border-bottom text-danger">
            <span><i class="fa-solid fa-star me-2"></i>Đánh giá khách hàng</span>
            <span class="badge bg-light text-dark">{{ $airConditioner->reviews->count() }}</span>
        </div>
        <div class="card-body p-4">
            @forelse($airConditioner->reviews as $review)
                <article class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <strong>{{ $review->user->name }}</strong>
                        <span class="small text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="text-warning my-2">
                        @for($star = 1; $star <= 5; $star++)
                            <i class="fa-{{ $star <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                        @endfor
                    </div>
                    <p class="mb-3 text-break">{{ $review->comment }}</p>
                    @if($review->admin_reply)
                        <div class="bg-light border-start border-3 border-danger rounded p-3 mb-3">
                            <strong class="small text-danger">Phản hồi cửa hàng</strong>
                            <p class="mb-1 mt-1 text-break">{{ $review->admin_reply }}</p>
                            @if($review->repliedBy)
                                <span class="small text-muted">{{ $review->repliedBy->name }} · {{ $review->replied_at?->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                    @endif
                    <form action="{{ route('admin.product-reviews.reply', $review) }}" method="POST">
                        @csrf
                        <label for="review-reply-{{ $review->id }}" class="form-label small fw-semibold">{{ $review->admin_reply ? 'Cập nhật phản hồi' : 'Trả lời đánh giá' }}</label>
                        <div class="input-group">
                            <textarea id="review-reply-{{ $review->id }}" name="admin_reply" class="form-control" rows="2" minlength="2" maxlength="2000" required>{{ old('admin_reply', $review->admin_reply) }}</textarea>
                            <button type="submit" class="btn btn-outline-danger align-self-stretch"><i class="fa-solid fa-reply me-1"></i>Gửi</button>
                        </div>
                    </form>
                </article>
            @empty
                <p class="text-muted mb-0">Chưa có đánh giá cho sản phẩm này.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const variantData = @json($variantData);
        const adminSpecBody = document.getElementById('admin-spec-body');
        const variantCards = document.querySelectorAll('.variant-selectable');
        const adminMainProductImg = document.getElementById('admin-main-product-img');
        const adminGalleryUrls = @json($galleryImageUrls);
        const adminGalleryNav = document.querySelectorAll('.admin-gallery-nav');
        const adminGalleryThumbnails = document.querySelectorAll('.admin-gallery-thumbnail');
        let adminGalleryIndex = 0;

        function showAdminGalleryImage(index) {
            if (!adminMainProductImg || adminGalleryUrls.length < 2) return;
            adminGalleryIndex = (index + adminGalleryUrls.length) % adminGalleryUrls.length;
            adminMainProductImg.src = adminGalleryUrls[adminGalleryIndex];
        }

        adminGalleryNav.forEach(button => {
            button.addEventListener('click', function () {
                showAdminGalleryImage(adminGalleryIndex + Number(this.dataset.direction));
            });
        });

        adminGalleryThumbnails.forEach(button => {
            button.addEventListener('click', function () {
                showAdminGalleryImage(Number(this.dataset.index));
            });
        });

        function renderAdminTable(variant) {
            if (!adminSpecBody || !variant) return;

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

            adminSpecBody.innerHTML = rows.map(([label, value]) => `
                <tr>
                    <td class="text-muted" style="width: 42%;">${label}</td>
                    <td class="fw-semibold text-dark">${value === null || value === undefined || value === '' ? '—' : value}</td>
                </tr>
            `).join('');
        }

        function selectVariant(variantId) {
            const selected = variantData.find(item => String(item.id) === String(variantId));
            if (!selected) return;

            variantCards.forEach(card => {
                const isSelected = String(card.dataset.variantId) === String(selected.id);
                card.classList.toggle('is-selected', isSelected);
            });

            renderAdminTable(selected);
        }

        if (variantCards.length) {
            variantCards.forEach(card => {
                card.addEventListener('click', function () {
                    selectVariant(this.dataset.variantId);
                });
            });

            selectVariant(variantCards[0].dataset.variantId);
        }
    });
</script>
@endsection