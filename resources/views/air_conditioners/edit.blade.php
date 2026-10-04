@extends('layouts.shop')

@section('content')
<style>
    .product-form-page { --form-ink: #172033; --form-line: #e7ebf2; }
    .product-form-section { scroll-margin-top: 155px; }
    .required-missing { border-color: #e21b23 !important; box-shadow: 0 0 0 .2rem rgba(226,27,35,.1) !important; }
</style>
<div class="container my-4 product-form-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Cập Nhật Điều Hòa</h3>
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

    <form action="{{ route('air_conditioners.update', $airConditioner->id) }}" method="POST" enctype="multipart/form-data" id="product-form">
        @csrf
        @method('PUT')
        <!-- 1. Thông tin chung -->
        <div class="card border-0 shadow-sm mb-4 product-form-section" id="basic-section">
            <div class="card-header bg-white fw-bold py-3 text-primary border-bottom">
                <i class="fa-solid fa-circle-info me-1"></i> 1. Thông tin sản phẩm chung
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $airConditioner->name) }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Thương hiệu <span class="text-danger">*</span></label>
                                <input type="text" name="brand" class="form-control" value="{{ old('brand', $airConditioner->brand) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Giá niêm yết tiêu chuẩn (đ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" value="{{ old('price', (int)$airConditioner->price) }}" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Xuất xứ</label>
                                <input type="text" name="origin" class="form-control" value="{{ old('origin', $airConditioner->origin ?? 'Thái Lan') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bảo hành</label>
                                <input type="text" name="warranty" class="form-control" value="{{ old('warranty', $airConditioner->warranty ?? '1 năm máy, 5 năm máy nén') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Bài viết mô tả chi tiết</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description', $airConditioner->description) }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-5 product-form-section" id="image-section">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ảnh sản phẩm (có thể chọn nhiều ảnh)</label>
                            <div class="border rounded p-3 text-center bg-light mb-2">
                                @php $displayMainImage = $airConditioner->primary_image_path ?? $airConditioner->image; @endphp
                                @if($displayMainImage)
                                    <img id="preview-img" src="{{ request()->getBaseUrl() . '/storage/' . $displayMainImage }}" class="img-fluid" style="max-height: 200px; object-fit: contain;">
                                @else
                                    <img id="preview-img" src="#" alt="Preview" class="img-fluid d-none" style="max-height: 200px; object-fit: contain;">
                                    <div id="preview-placeholder" class="text-muted py-4">
                                        <i class="fa-regular fa-image fs-1 d-block mb-1"></i> Chưa có ảnh
                                    </div>
                                @endif
                                <div id="existing-gallery-preview" class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                                    @forelse($airConditioner->images as $productImage)
                                        <div class="position-relative gallery-image-item">
                                            <img src="{{ request()->getBaseUrl() . '/storage/' . $productImage->image_path }}" alt="Ảnh sản phẩm" title="Ảnh sản phẩm" style="width: 58px; height: 58px; object-fit: cover; border-radius: 4px;">
                                            <button type="button" class="btn btn-danger btn-sm remove-gallery-image position-absolute top-0 end-0 p-0" data-image-id="{{ $productImage->id }}" aria-label="Xóa ảnh" title="Xóa ảnh" style="width: 20px; height: 20px; line-height: 1;"><i class="fa-solid fa-xmark"></i></button>
                                        </div>
                                    @empty
                                        @if($airConditioner->image)
                                            <div class="position-relative gallery-image-item">
                                                <img src="{{ request()->getBaseUrl() . '/storage/' . $airConditioner->image }}" alt="Ảnh sản phẩm" title="Ảnh sản phẩm" style="width: 58px; height: 58px; object-fit: cover; border-radius: 4px;">
                                                <button type="button" class="btn btn-danger btn-sm remove-gallery-image position-absolute top-0 end-0 p-0" data-legacy-image="1" aria-label="Xóa ảnh" title="Xóa ảnh" style="width: 20px; height: 20px; line-height: 1;"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                        @endif
                                    @endforelse
                                </div>
                                <div id="selected-gallery-preview" class="d-flex flex-wrap justify-content-center gap-2 mt-2"></div>
                                <div id="removed-gallery-inputs"></div>
                            </div>
                            <input type="file" name="images[]" id="image-input" class="form-control" accept="image/*" multiple>
                            <small id="selected-image-count" class="form-text text-muted">Ảnh mới sẽ được thêm vào gallery hiện tại.</small>
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
                @forelse($airConditioner->variants as $index => $variant)
                    @php $specs = $variant->specifications ?? []; @endphp
                    <div class="variant-item border rounded p-3 mb-3 bg-light" data-index="{{ $index }}">
                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-cube text-primary me-2"></i>Phiên bản công suất #{{ $index + 1 }}</h6>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-variant"><i class="fa-solid fa-trash-can me-1"></i>Xóa phiên bản này</button>
                        </div>

                        <!-- Thông tin cơ bản biến thể -->
                        <div class="row g-2 mb-3" id="variant-fields">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Tên công suất *</label>
                                <input type="text" name="variants[{{ $index }}][capacity_name]" class="form-control" value="{{ $variant->capacity_name }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Giá bán thực tế (đ) *</label>
                                <input type="number" name="variants[{{ $index }}][price]" class="form-control" value="{{ (int)$variant->price }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Tồn kho *</label>
                                <input type="number" name="variants[{{ $index }}][stock]" class="form-control text-center" value="{{ $variant->stock }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Trọng lượng (g) *</label>
                                <input type="number" name="variants[{{ $index }}][weight]" class="form-control" value="{{ $variant->weight ?? 55000 }}" required>
                            </div>
                        </div>

                        <!-- Accordion Nhập Thông Số Kỹ Thuật Chi Tiết -->
                        <div class="accordion" id="{{ $index === 0 ? 'spec-section' : 'accordionSpec' . $index }}">
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-white border fw-bold text-secondary rounded py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpec{{ $index }}">
                                        <i class="fa-solid fa-list-check me-2 text-info"></i> Thông Số Kỹ Thuật Chi Tiết (Dàn nóng, Dàn lạnh, Công suất...)
                                    </button>
                                </h2>
                                <div id="collapseSpec{{ $index }}" class="accordion-collapse collapse bg-white border border-top-0 p-3 rounded-bottom">
                                    <div class="row g-3">
                                        <!-- Nhóm 1: Thông số chung -->
                                        <div class="col-12"><strong class="text-primary"><i class="fa-solid fa-sliders me-1"></i> Thông số kỹ thuật chung</strong></div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Loại máy</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][machine_type]" class="form-control form-control-sm" value="{{ $specs['machine_type'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Công nghệ Inverter</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][inverter]" class="form-control form-control-sm" value="{{ $specs['inverter'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Công suất làm lạnh</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][cooling_capacity]" class="form-control form-control-sm" value="{{ $specs['cooling_capacity'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Phạm vi làm lạnh hiệu quả</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][effective_area]" class="form-control form-control-sm" value="{{ $specs['effective_area'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Điện năng tiêu thụ</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][power_consumption]" class="form-control form-control-sm" value="{{ $specs['power_consumption'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Tiết kiệm điện (Sao)</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][energy_stars]" class="form-control form-control-sm" value="{{ $specs['energy_stars'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Hiệu suất năng lượng (CSPF)</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][cspf]" class="form-control form-control-sm" value="{{ $specs['cspf'] ?? '' }}">
                                        </div>

                                        <!-- Nhóm 2: Dàn lạnh -->
                                        <div class="col-12 mt-3"><strong class="text-primary"><i class="fa-solid fa-box me-1"></i> Thông tin dàn lạnh</strong></div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Model Dàn Lạnh</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][indoor_model]" class="form-control form-control-sm" value="{{ $specs['indoor_model'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Kích thước Dàn Lạnh</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][indoor_dimensions]" class="form-control form-control-sm" value="{{ $specs['indoor_dimensions'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Trọng lượng Dàn Lạnh</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][indoor_weight]" class="form-control form-control-sm" value="{{ $specs['indoor_weight'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Độ ồn Dàn Lạnh</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][indoor_noise]" class="form-control form-control-sm" value="{{ $specs['indoor_noise'] ?? '' }}">
                                        </div>

                                        <!-- Nhóm 3: Dàn nóng -->
                                        <div class="col-12 mt-3"><strong class="text-primary"><i class="fa-solid fa-fan me-1"></i> Thông tin dàn nóng</strong></div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Model Dàn Nóng</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][outdoor_model]" class="form-control form-control-sm" value="{{ $specs['outdoor_model'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Kích thước Dàn Nóng</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][outdoor_dimensions]" class="form-control form-control-sm" value="{{ $specs['outdoor_dimensions'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Trọng lượng Dàn Nóng</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][outdoor_weight]" class="form-control form-control-sm" value="{{ $specs['outdoor_weight'] ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Độ ồn Dàn Nóng</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][outdoor_noise]" class="form-control form-control-sm" value="{{ $specs['outdoor_noise'] ?? '' }}">
                                        </div>

                                        <!-- Nhóm 4: Lắp đặt & Môi chất -->
                                        <div class="col-12 mt-3"><strong class="text-primary"><i class="fa-solid fa-gears me-1"></i> Lắp đặt & Môi chất</strong></div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Môi chất lạnh (Gas)</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][refrigerant]" class="form-control form-control-sm" value="{{ $specs['refrigerant'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Chiều dài ống tối đa</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][max_pipe_length]" class="form-control form-control-sm" value="{{ $specs['max_pipe_length'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Chênh lệch độ cao tối đa</label>
                                            <input type="text" name="variants[{{ $index }}][specifications][max_elevation_diff]" class="form-control form-control-sm" value="{{ $specs['max_elevation_diff'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="text-center py-3 text-muted">Chưa có phiên bản nào, vui lòng bấm nút "Thêm phiên bản BTU".</div>
                @endforelse
            </div>
        </div>

        <div class="d-flex justify-content-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold"><i class="fa-solid fa-floppy-disk me-2"></i>Lưu Cập Nhật</button>
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

    const imageInput = document.getElementById('image-input');
    let selectedFiles = [];
    imageInput.addEventListener('change', function(e) {
        const filesByKey = new Map(selectedFiles.map((file) => [`${file.name}-${file.size}-${file.lastModified}`, file]));
        Array.from(e.target.files).forEach((file) => {
            filesByKey.set(`${file.name}-${file.size}-${file.lastModified}`, file);
        });
        selectedFiles = Array.from(filesByKey.values());
        const transfer = new DataTransfer();
        selectedFiles.forEach((file) => transfer.items.add(file));
        imageInput.files = transfer.files;

        const files = selectedFiles;
        const preview = document.getElementById('selected-gallery-preview');
        const count = document.getElementById('selected-image-count');
        preview.innerHTML = '';
        count.textContent = files.length ? `Sẽ thêm ${files.length} ảnh mới vào gallery.` : 'Ảnh mới sẽ được thêm vào gallery hiện tại.';

        if (files.length) {
            const mainImage = document.getElementById('preview-img');
            mainImage.src = URL.createObjectURL(files[0]);
            mainImage.classList.remove('d-none');
            const placeholder = document.getElementById('preview-placeholder');
            if (placeholder) placeholder.classList.add('d-none');

            files.forEach((file) => {
                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = file.name;
                image.title = file.name;
                image.style.cssText = 'width: 58px; height: 58px; object-fit: cover; border-radius: 4px;';
                preview.appendChild(image);
            });
        }
    });

    document.addEventListener('click', function(e) {
        const removeButton = e.target.closest('.remove-gallery-image');
        if (!removeButton) return;

        const inputs = document.getElementById('removed-gallery-inputs');
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        if (removeButton.dataset.imageId) {
            hiddenInput.name = 'remove_images[]';
            hiddenInput.value = removeButton.dataset.imageId;
        } else {
            hiddenInput.name = 'remove_legacy_image';
            hiddenInput.value = '1';
        }
        inputs.appendChild(hiddenInput);
        removeButton.closest('.gallery-image-item').remove();

        const mainImage = document.getElementById('preview-img');
        const nextImage = document.querySelector('#existing-gallery-preview img, #selected-gallery-preview img');
        const placeholder = document.getElementById('preview-placeholder');
        if (nextImage) {
            mainImage.src = nextImage.src;
            mainImage.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        } else {
            mainImage.classList.add('d-none');
            if (placeholder) placeholder.classList.remove('d-none');
        }
    });

    let variantIndex = {{ count($airConditioner->variants) }};
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
                        <label class="form-label small fw-bold">Trọng lượng (g) *</label>
                        <input type="number" name="variants[${variantIndex}][weight]" class="form-control" value="45000" required>
                    </div>
                </div>

                <div class="accordion" id="accordionSpec${variantIndex}">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-white border fw-bold text-secondary rounded py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpec${variantIndex}">
                                <i class="fa-solid fa-list-check me-2 text-info"></i> Thông Số Kỹ Thuật Chi Tiết (Dàn nóng, Dàn lạnh, Công suất...)
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