@extends('layouts.shop')

@section('content')
<style>
    .product-form-page { --form-ink: #172033; --form-line: #e7ebf2; }
    .product-form-section { scroll-margin-top: 155px; }
    .required-missing { border-color: #e21b23 !important; box-shadow: 0 0 0 .2rem rgba(226,27,35,.1) !important; }
</style>
<div class="container my-4 product-form-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0"><i class="fa-solid fa-square-plus me-2 text-primary"></i>Thêm Mới Điều Hòa</h3>
        <a href="{{ route('air_conditioners.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('air_conditioners.store') }}" method="POST" id="product-form">
        @csrf
        <!-- 1. Thông tin chung sản phẩm -->
        <div class="card border-0 shadow-sm mb-4 product-form-section" id="basic-section">
            <div class="card-header bg-white fw-bold py-3 text-primary border-bottom">
                <i class="fa-solid fa-circle-info me-1"></i> 1. Thông tin sản phẩm chung
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="VD: Điều hòa Daikin Inverter FTKM Series">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Thương hiệu <span class="text-danger">*</span></label>
                                <input type="text" name="brand" class="form-control" value="{{ old('brand', 'Daikin') }}" required placeholder="VD: Daikin, Casper">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Giá niêm yết tiêu chuẩn (đ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" value="{{ old('price') }}" required placeholder="15000000">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Xuất xứ</label>
                                <input type="text" name="origin" class="form-control" value="{{ old('origin', 'Thái Lan') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bảo hành</label>
                                <input type="text" name="warranty" class="form-control" value="{{ old('warranty', '1 năm máy, 5 năm máy nén') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Bài viết mô tả chi tiết</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Nhập bài viết đánh giá, đặc điểm nổi bật...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-5 product-form-section" id="image-section">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Link ảnh sản phẩm (tối đa 5 ảnh)</label>
                            <div class="border rounded p-3 text-center bg-light mb-2">
                                <img id="preview-img" src="#" alt="Preview" class="img-fluid d-none" style="max-height: 200px; object-fit: contain;">
                                <div id="preview-placeholder" class="text-muted py-4">
                                    <i class="fa-regular fa-image fs-1 d-block mb-1"></i>
                                    <small>Xem trước hình ảnh hiển thị tại đây</small>
                                </div>
                                <div id="selected-gallery-preview" class="d-flex flex-wrap justify-content-center gap-2 mt-2"></div>
                            </div>
                            <div class="row g-2">
                                @for($imageIndex = 0; $imageIndex < 5; $imageIndex++)
                                    <div class="col-12"><input type="url" name="images[]" class="form-control product-image-url" placeholder="https://example.com/anh-{{ $imageIndex + 1 }}.jpg" value="{{ old('images.'.$imageIndex) }}"></div>
                                @endfor
                            </div>
                            <small class="form-text text-muted">Dán đường link ảnh công khai từ internet.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Danh sách Biến thể BTU & Thông số kỹ thuật chi tiết -->
        <div class="card border-0 shadow-sm mb-4 product-form-section" id="variant-section">
            <div class="card-header bg-white d-flex justify-content-between align-items-center fw-bold py-3 text-primary border-bottom">
                <span><i class="fa-solid fa-layer-group me-1"></i> 2. Danh sách phân loại công suất (Biến thể BTU)</span>
                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" id="add-variant">
                    <i class="fa-solid fa-plus me-1"></i> Thêm phiên bản BTU
                </button>
            </div>
            <div class="card-body" id="variant-container">
                
                <!-- Biến thể mẫu số 1 -->
                <div class="variant-item border rounded p-3 mb-3 bg-light" data-index="0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-cube text-primary me-2"></i>Phiên bản công suất #1</h6>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-variant"><i class="fa-solid fa-trash-can me-1"></i>Xóa phiên bản này</button>
                    </div>

                    <!-- Thông tin cơ bản biến thể -->
                    <div class="row g-2 mb-3" id="variant-fields">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tên công suất *</label>
                            <input type="text" name="variants[0][capacity_name]" class="form-control" placeholder="18.000 BTU (2 HP)" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Giá bán thực tế (đ) *</label>
                            <input type="number" name="variants[0][price]" class="form-control" placeholder="18500000" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Tồn kho *</label>
                            <input type="number" name="variants[0][stock]" class="form-control text-center" value="10" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Tổng trọng lượng (g) *</label>
                            <input type="number" name="variants[0][weight]" class="form-control" value="55000" required placeholder="Tính bằng gram">
                        </div>
                    </div>

                    <!-- Accordion Nhập Thông Số Kỹ Thuật Chi Tiết -->
                    <div class="accordion" id="spec-section">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-white border fw-bold text-secondary rounded py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpec0">
                                    <i class="fa-solid fa-list-check me-2 text-info"></i> Nhập Thông Số Kỹ Thuật Chi Tiết (Dàn nóng, Dàn lạnh, Công suất...)
                                </button>
                            </h2>
                            <div id="collapseSpec0" class="accordion-collapse collapse bg-white border border-top-0 p-3 rounded-bottom">
                                <div class="row g-3">
                                    <!-- Nhóm 1: Thông số chung -->
                                    <div class="col-12"><strong class="text-primary"><i class="fa-solid fa-sliders me-1"></i> Thông số kỹ thuật chung</strong></div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Loại máy</label>
                                        <input type="text" name="variants[0][specifications][machine_type]" class="form-control form-control-sm" placeholder="VD: HP (2 chiều) / CO (1 chiều)">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Công nghệ Inverter</label>
                                        <input type="text" name="variants[0][specifications][inverter]" class="form-control form-control-sm" placeholder="VD: Có / Không">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Công suất làm lạnh (kW / BTU)</label>
                                        <input type="text" name="variants[0][specifications][cooling_capacity]" class="form-control form-control-sm" placeholder="VD: 5.28 kW (18,000 BTU)">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Phạm vi làm lạnh hiệu quả</label>
                                        <input type="text" name="variants[0][specifications][effective_area]" class="form-control form-control-sm" placeholder="VD: <= 27 m²">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Điện năng tiêu thụ</label>
                                        <input type="text" name="variants[0][specifications][power_consumption]" class="form-control form-control-sm" placeholder="VD: Lạnh: 1,260 (220 - 1,700) W">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Tiết kiệm điện (Sao)</label>
                                        <input type="text" name="variants[0][specifications][energy_stars]" class="form-control form-control-sm" placeholder="VD: 5 sao (★★★★★)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Hiệu suất năng lượng (CSPF)</label>
                                        <input type="text" name="variants[0][specifications][cspf]" class="form-control form-control-sm" placeholder="VD: 7.16">
                                    </div>

                                    <!-- Nhóm 2: Dàn lạnh -->
                                    <div class="col-12 mt-3"><strong class="text-primary"><i class="fa-solid fa-box me-1"></i> Thông tin dàn lạnh</strong></div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Model Dàn Lạnh</label>
                                        <input type="text" name="variants[0][specifications][indoor_model]" class="form-control form-control-sm" placeholder="VD: FTKM50AVMV">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Kích thước Dàn Lạnh (Cao x Rộng x Dày)</label>
                                        <input type="text" name="variants[0][specifications][indoor_dimensions]" class="form-control form-control-sm" placeholder="VD: 299 x 1103 x 280 mm">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Trọng lượng Dàn Lạnh (kg)</label>
                                        <input type="text" name="variants[0][specifications][indoor_weight]" class="form-control form-control-sm" placeholder="VD: 15 kg">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Độ ồn Dàn Lạnh (dbA)</label>
                                        <input type="text" name="variants[0][specifications][indoor_noise]" class="form-control form-control-sm" placeholder="VD: 45 / 40 / 35 / 25">
                                    </div>

                                    <!-- Nhóm 3: Dàn nóng -->
                                    <div class="col-12 mt-3"><strong class="text-primary"><i class="fa-solid fa-fan me-1"></i> Thông tin dàn nóng</strong></div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Model Dàn Nóng</label>
                                        <input type="text" name="variants[0][specifications][outdoor_model]" class="form-control form-control-sm" placeholder="VD: RKM50AVMV">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Kích thước Dàn Nóng (Cao x Rộng x Dày)</label>
                                        <input type="text" name="variants[0][specifications][outdoor_dimensions]" class="form-control form-control-sm" placeholder="VD: 595 x 845 x 300 mm">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Trọng lượng Dàn Nóng (kg)</label>
                                        <input type="text" name="variants[0][specifications][outdoor_weight]" class="form-control form-control-sm" placeholder="VD: 40 kg">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Độ ồn Dàn Nóng (dbA)</label>
                                        <input type="text" name="variants[0][specifications][outdoor_noise]" class="form-control form-control-sm" placeholder="VD: 49 / 45">
                                    </div>

                                    <!-- Nhóm 4: Lắp đặt & Môi chất -->
                                    <div class="col-12 mt-3"><strong class="text-primary"><i class="fa-solid fa-gears me-1"></i> Lắp đặt & Môi chất</strong></div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Môi chất lạnh (Gas)</label>
                                        <input type="text" name="variants[0][specifications][refrigerant]" class="form-control form-control-sm" placeholder="VD: R32">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Chiều dài ống tối đa (m)</label>
                                        <input type="text" name="variants[0][specifications][max_pipe_length]" class="form-control form-control-sm" placeholder="VD: 30 m">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Chênh lệch độ cao tối đa (m)</label>
                                        <input type="text" name="variants[0][specifications][max_elevation_diff]" class="form-control form-control-sm" placeholder="VD: 20 m">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="d-flex justify-content-end mb-5">
            <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold"><i class="fa-solid fa-floppy-disk me-2"></i>Lưu Sản Phẩm</button>
        </div>
    </form>
</div>

<script>
    (() => {
        const form = document.getElementById('product-form');
        let isDirty = false;

        form.addEventListener('input', () => {
            isDirty = true;
            form.querySelectorAll('[required]').forEach((field) => field.classList.toggle('required-missing', !field.checkValidity()));
        });
        form.addEventListener('change', () => { isDirty = true; });
        form.addEventListener('submit', () => { isDirty = false; });
        window.addEventListener('beforeunload', (event) => {
            if (isDirty) { event.preventDefault(); event.returnValue = ''; }
        });
    })();

    document.querySelectorAll('.product-image-url').forEach((input) => {
        input.addEventListener('input', () => {
            const firstUrl = Array.from(document.querySelectorAll('.product-image-url')).map((field) => field.value.trim()).find(Boolean);
            const preview = document.getElementById('preview-img');
            const placeholder = document.getElementById('preview-placeholder');
            if (firstUrl) {
                preview.src = firstUrl;
                preview.classList.remove('d-none');
                placeholder.classList.add('d-none');
            } else {
                preview.classList.add('d-none');
                placeholder.classList.remove('d-none');
            }
        });
    });

    // Dynamic thêm nhiều biến thể
    let variantIndex = 1;
    document.getElementById('add-variant').addEventListener('click', function() {
        const container = document.getElementById('variant-container');
        const newHtml = `
            <div class="variant-item border rounded p-3 mb-3 bg-light" data-index="${variantIndex}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-cube text-primary me-2"></i>Phiên bản công suất #${variantIndex + 1}</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-variant"><i class="fa-solid fa-trash-can me-1"></i>Xóa phiên bản này</button>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Tên công suất *</label>
                        <input type="text" name="variants[${variantIndex}][capacity_name]" class="form-control" placeholder="12.000 BTU (1.5 HP)" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Giá bán thực tế (đ) *</label>
                        <input type="number" name="variants[${variantIndex}][price]" class="form-control" placeholder="13500000" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Tồn kho *</label>
                        <input type="number" name="variants[${variantIndex}][stock]" class="form-control text-center" value="10" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Tổng trọng lượng (g) *</label>
                        <input type="number" name="variants[${variantIndex}][weight]" class="form-control" value="45000" required>
                    </div>
                </div>

                <div class="accordion" id="accordionSpec${variantIndex}">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-white border fw-bold text-secondary rounded py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpec${variantIndex}">
                                <i class="fa-solid fa-list-check me-2 text-info"></i> Nhập Thông Số Kỹ Thuật Chi Tiết (Dàn nóng, Dàn lạnh, Công suất...)
                            </button>
                        </h2>
                        <div id="collapseSpec${variantIndex}" class="accordion-collapse collapse bg-white border border-top-0 p-3 rounded-bottom">
                            <div class="row g-3">
                                <div class="col-12"><strong class="text-primary"><i class="fa-solid fa-sliders me-1"></i> Thông số kỹ thuật chung</strong></div>
                                <div class="col-md-3"><label class="form-label small">Loại máy</label><input type="text" name="variants[${variantIndex}][specifications][machine_type]" class="form-control form-control-sm"></div>
                                <div class="col-md-3"><label class="form-label small">Công nghệ Inverter</label><input type="text" name="variants[${variantIndex}][specifications][inverter]" class="form-control form-control-sm"></div>
                                <div class="col-md-3"><label class="form-label small">Công suất làm lạnh</label><input type="text" name="variants[${variantIndex}][specifications][cooling_capacity]" class="form-control form-control-sm"></div>
                                <div class="col-md-3"><label class="form-label small">Phạm vi hiệu quả</label><input type="text" name="variants[${variantIndex}][specifications][effective_area]" class="form-control form-control-sm"></div>
                                <div class="col-md-4"><label class="form-label small">Điện năng tiêu thụ</label><input type="text" name="variants[${variantIndex}][specifications][power_consumption]" class="form-control form-control-sm"></div>
                                <div class="col-md-4"><label class="form-label small">Tiết kiệm điện</label><input type="text" name="variants[${variantIndex}][specifications][energy_stars]" class="form-control form-control-sm"></div>
                                <div class="col-md-4"><label class="form-label small">Hiệu suất (CSPF)</label><input type="text" name="variants[${variantIndex}][specifications][cspf]" class="form-control form-control-sm"></div>

                                <div class="col-12 mt-3"><strong class="text-primary"><i class="fa-solid fa-box me-1"></i> Thông tin dàn lạnh</strong></div>
                                <div class="col-md-3"><label class="form-label small">Model Dàn Lạnh</label><input type="text" name="variants[${variantIndex}][specifications][indoor_model]" class="form-control form-control-sm"></div>
                                <div class="col-md-3"><label class="form-label small">Kích thước Dàn Lạnh</label><input type="text" name="variants[${variantIndex}][specifications][indoor_dimensions]" class="form-control form-control-sm"></div>
                                <div class="col-md-3"><label class="form-label small">Trọng lượng Dàn Lạnh</label><input type="text" name="variants[${variantIndex}][specifications][indoor_weight]" class="form-control form-control-sm"></div>
                                <div class="col-md-3"><label class="form-label small">Độ ồn Dàn Lạnh</label><input type="text" name="variants[${variantIndex}][specifications][indoor_noise]" class="form-control form-control-sm"></div>

                                <div class="col-12 mt-3"><strong class="text-primary"><i class="fa-solid fa-fan me-1"></i> Thông tin dàn nóng</strong></div>
                                <div class="col-md-3"><label class="form-label small">Model Dàn Nóng</label><input type="text" name="variants[${variantIndex}][specifications][outdoor_model]" class="form-control form-control-sm"></div>
                                <div class="col-md-3"><label class="form-label small">Kích thước Dàn Nóng</label><input type="text" name="variants[${variantIndex}][specifications][outdoor_dimensions]" class="form-control form-control-sm"></div>
                                <div class="col-md-3"><label class="form-label small">Trọng lượng Dàn Nóng</label><input type="text" name="variants[${variantIndex}][specifications][outdoor_weight]" class="form-control form-control-sm"></div>
                                <div class="col-md-3"><label class="form-label small">Độ ồn Dàn Nóng</label><input type="text" name="variants[${variantIndex}][specifications][outdoor_noise]" class="form-control form-control-sm"></div>

                                <div class="col-12 mt-3"><strong class="text-primary"><i class="fa-solid fa-gears me-1"></i> Lắp đặt & Môi chất</strong></div>
                                <div class="col-md-4"><label class="form-label small">Môi chất lạnh (Gas)</label><input type="text" name="variants[${variantIndex}][specifications][refrigerant]" class="form-control form-control-sm"></div>
                                <div class="col-md-4"><label class="form-label small">Chiều dài ống max</label><input type="text" name="variants[${variantIndex}][specifications][max_pipe_length]" class="form-control form-control-sm"></div>
                                <div class="col-md-4"><label class="form-label small">Chênh lệch độ cao max</label><input type="text" name="variants[${variantIndex}][specifications][max_elevation_diff]" class="form-control form-control-sm"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', newHtml);
        variantIndex++;
    });

    // Xóa biến thể
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.classList.contains('remove-variant') || e.target.closest('.remove-variant'))) {
            const items = document.querySelectorAll('.variant-item');
            if (items.length > 1) {
                e.target.closest('.variant-item').remove();
            } else {
                alert('Sản phẩm phải có ít nhất 1 phiên bản công suất!');
            }
        }
    });
</script>
@endsection