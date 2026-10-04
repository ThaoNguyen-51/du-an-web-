<?php

namespace App\Http\Controllers;

use App\Models\AirConditioner;
use App\Models\AirConditionerVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\AdminActivityLogger;

class AirConditionerController extends Controller
{
    private function syncProductGallery(AirConditioner $airConditioner, array $imageUrls = [], bool $append = false): void
    {
        $paths = collect($imageUrls)->map(fn ($url) => trim($url))->filter()->values()->all();

        if (empty($paths)) {
            return;
        }

        if ($append) {
            if ($airConditioner->images()->count() === 0 && $airConditioner->image) {
                $airConditioner->images()->create([
                    'image_path' => $airConditioner->image,
                    'sort_order' => 0,
                ]);
            }
            $sortOrder = $airConditioner->images()->count();
        } else {
            foreach ($airConditioner->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }
            $airConditioner->images()->delete();
            $sortOrder = 0;
        }

        foreach ($paths as $path) {
            $airConditioner->images()->create([
                'image_path' => $path,
                'sort_order' => $sortOrder++,
            ]);
        }

        $airConditioner->image = $airConditioner->images()->orderBy('sort_order')->value('image_path');
        $airConditioner->save();
    }

    public function index(Request $request)
    {
        $query = AirConditioner::with(['variants', 'images']);

        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->input('stock') === 'low') {
            $query->whereHas('variants', fn ($variantQuery) => $variantQuery->whereBetween('stock', [1, 5]));
            $query->with(['variants' => fn ($variantQuery) => $variantQuery->whereBetween('stock', [1, 5])]);
        } elseif ($request->input('stock') === 'out') {
            $query->whereHas('variants', fn ($variantQuery) => $variantQuery->where('stock', '<=', 0));
            $query->with(['variants' => fn ($variantQuery) => $variantQuery->where('stock', '<=', 0)]);
        } elseif ($request->input('stock') === 'available') {
            $query->whereHas('variants', fn ($variantQuery) => $variantQuery->where('stock', '>', 5));
        }

        match ($request->input('sort')) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name_asc' => $query->orderBy('name'),
            'stock_asc' => $query->withMin('variants', 'stock')->orderBy('variants_min_stock'),
            default => $query->latest(),
        };

        $airConditioners = $query->get();
        $brands = AirConditioner::query()
            ->whereNotNull('brand')
            ->whereRaw("TRIM(brand) <> ''")
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');
        $lowStockCount = AirConditionerVariant::whereBetween('stock', [1, 5])->count();
        $outOfStockCount = AirConditionerVariant::where('stock', '<=', 0)->count();

        return view('air_conditioners.index', compact('airConditioners', 'brands', 'lowStockCount', 'outOfStockCount'));
    }

    public function create()
    {
        return view('air_conditioners.create');
    }

    public function store(Request $request, AdminActivityLogger $activityLogger)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'price' => 'required|numeric',
            'images' => 'nullable|array',
            'images.*' => 'nullable|url|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.capacity_name' => 'required|string',
            'variants.*.price' => 'required|numeric',
            'variants.*.weight' => 'required|integer',
        ]);

        $productData = $request->only(['name', 'brand', 'price', 'description', 'origin', 'warranty']);

        $airConditioner = AirConditioner::create($productData);

        if ($request->filled('images')) {
            $this->syncProductGallery($airConditioner, $request->input('images', []));
        }

        foreach ($request->variants as $variantData) {
            $airConditioner->variants()->create([
                'capacity_name' => $variantData['capacity_name'],
                'price' => $variantData['price'],
                'stock' => $variantData['stock'] ?? 10,
                'weight' => $variantData['weight'] ?? 25000,
                'specifications' => $variantData['specifications'] ?? [],
            ]);
        }
        $activityLogger->record('product_created', "Tạo sản phẩm {$airConditioner->name}", $airConditioner);

        return redirect()->route('air_conditioners.index')->with('success', 'Thêm sản phẩm thành công!');
    }
    public function show($id)
    {
        $airConditioner = AirConditioner::with(['variants', 'images', 'reviews.user', 'reviews.repliedBy'])->findOrFail($id);
        return view('air_conditioners.show', compact('airConditioner'));
    }

    public function edit($id)
    {
        $airConditioner = AirConditioner::with(['variants', 'images'])->findOrFail($id);
        return view('air_conditioners.edit', compact('airConditioner'));
    }

    public function update(Request $request, $id, AdminActivityLogger $activityLogger)
    {
        $airConditioner = AirConditioner::with('variants')->findOrFail($id);
        $beforeVariants = $airConditioner->variants->keyBy('id')->map(fn ($variant) => $variant->only(['price', 'stock']));
        $beforeProductPrice = (string) $airConditioner->price;

        $request->validate([
            'name'                  => 'required|string|max:255',
            'brand'                 => 'required|string|max:255',
            'price'                 => 'required|numeric',
            'weight'                => 'nullable|integer|min:100',
            'image'                 => 'nullable|url|max:2048',
            'images'                => 'nullable|array',
            'images.*'              => 'nullable|url|max:2048',
            'remove_images'         => 'nullable|array',
            'remove_images.*'       => 'integer',
            'remove_legacy_image'   => 'nullable|boolean',
            'description'           => 'nullable|string',
            'origin'                => 'nullable|string',
            'warranty'              => 'nullable|string',
            'room_size'             => 'nullable|string',
            'inverter_type'         => 'nullable|string',
            'type'                  => 'nullable|string',
            'power_consumption'     => 'nullable|string',
            'energy_rating'         => 'nullable|string',
            'antibacterial_feature' => 'nullable|string',
            'cooling_feature'       => 'nullable|string',
            'variants'              => 'nullable|array',
        ]);

        $data = $request->only([
            'name', 'brand', 'price', 'weight', 'description', // BỔ SUNG 'weight'
            'origin', 'warranty', 'room_size', 'inverter_type',
            'type', 'power_consumption', 'energy_rating',
            'antibacterial_feature', 'cooling_feature'
        ]);

        // Nếu người dùng không nhập cân nặng thì mặc định 25000g (25kg)
        $data['weight'] = $request->input('weight', 25000);
        $legacyImagePath = $airConditioner->image;

        if ($request->filled('images')) {
            $this->syncProductGallery($airConditioner, $request->input('images', []), true);
        }

        $airConditioner->update($data);

        $removeImageIds = array_map('intval', $request->input('remove_images', []));
        $removedImagePaths = [];
        $removedGalleryImage = false;
        if ($removeImageIds) {
            $imagesToRemove = $airConditioner->images()->whereIn('id', $removeImageIds)->get();
            foreach ($imagesToRemove as $productImage) {
                Storage::disk('public')->delete($productImage->image_path);
                $removedImagePaths[] = $productImage->image_path;
                $productImage->delete();
            $removedGalleryImage = true;
            }
        }

        if ($request->boolean('remove_legacy_image') && $legacyImagePath) {
            $legacyGalleryImage = $airConditioner->images()->where('image_path', $legacyImagePath)->first();
            if ($legacyGalleryImage) {
                Storage::disk('public')->delete($legacyGalleryImage->image_path);
                $legacyGalleryImage->delete();
                $removedImagePaths[] = $legacyImagePath;
            } elseif (!in_array($legacyImagePath, $removedImagePaths, true)) {
                Storage::disk('public')->delete($legacyImagePath);
            }
        }

        $remainingPrimaryImage = $airConditioner->images()->orderBy('sort_order')->value('image_path');
        if ($remainingPrimaryImage) {
            $airConditioner->image = $remainingPrimaryImage;
        } elseif ($removedGalleryImage || $request->boolean('remove_legacy_image')) {
            $airConditioner->image = null;
        }
        $airConditioner->save();

        // Xử lý biến thể BTU
        if ($request->has('variants')) {
            $updatedVariantIds = [];

            foreach ($request->variants as $variantData) {
                if (isset($variantData['id'])) {
                    $variant = AirConditionerVariant::find($variantData['id']);
                    if ($variant) {
                        $variant->update([
                            'capacity_name' => $variantData['capacity_name'],
                            'price'         => $variantData['price'],
                            'stock'         => $variantData['stock'],
                            'weight'        => $variantData['weight'] ?? $variant->weight ?? 25000,
                            'specifications' => $variantData['specifications'] ?? $variant->specifications ?? [],
                        ]);
                        $updatedVariantIds[] = $variant->id;
                    }
                } else {
                    $newVariant = $airConditioner->variants()->create([
                        'capacity_name' => $variantData['capacity_name'],
                        'price'         => $variantData['price'],
                        'stock'         => $variantData['stock'],
                        'weight'        => $variantData['weight'] ?? 25000,
                        'specifications' => $variantData['specifications'] ?? [],
                    ]);
                    $updatedVariantIds[] = $newVariant->id;
                }
            }

            // Xóa biến thể không còn tồn tại trong form
            $airConditioner->variants()->whereNotIn('id', $updatedVariantIds)->delete();
        } else {
            $airConditioner->variants()->delete();
        }

        $airConditioner->load('variants');
        $activityLogger->record('product_updated', "Sửa sản phẩm {$airConditioner->name}", $airConditioner);
        $priceChanges = [];
        $stockChanges = [];
        foreach ($airConditioner->variants as $variant) {
            $before = $beforeVariants->get($variant->id);
            if (!$before) {
                continue;
            }
            if ((string) $before['price'] !== (string) $variant->price) {
                $priceChanges[$variant->capacity_name] = [$before['price'], $variant->price];
            }
            if ((int) $before['stock'] !== (int) $variant->stock) {
                $stockChanges[$variant->capacity_name] = [$before['stock'], $variant->stock];
            }
        }
        if ($priceChanges) {
            $activityLogger->record('price_changed', "Sửa giá sản phẩm {$airConditioner->name}", $airConditioner, ['changes' => $priceChanges]);
        }
        if ($beforeProductPrice !== (string) $airConditioner->price && !$priceChanges) {
            $activityLogger->record('price_changed', "Sửa giá sản phẩm {$airConditioner->name}", $airConditioner, [
                'product_price' => [$beforeProductPrice, (string) $airConditioner->price],
            ]);
        }
        if ($stockChanges) {
            $activityLogger->record('stock_changed', "Sửa tồn kho sản phẩm {$airConditioner->name}", $airConditioner, ['changes' => $stockChanges]);
        }

        return redirect()->route('air_conditioners.index')->with('success', 'Cập nhật điều hòa thành công!');
    }

    public function destroy($id, AdminActivityLogger $activityLogger)
    {
        $airConditioner = AirConditioner::findOrFail($id);
        $activityLogger->record('product_deleted', "Xóa sản phẩm {$airConditioner->name}", $airConditioner);

        if ($airConditioner->image) {
            Storage::disk('public')->delete($airConditioner->image);
        }

        foreach ($airConditioner->images as $productImage) {
            Storage::disk('public')->delete($productImage->image_path);
        }

        foreach ($airConditioner->variants as $variant) {
            if ($variant->image) {
                Storage::disk('public')->delete($variant->image);
            }
        }

        $airConditioner->delete();

        return redirect()->route('air_conditioners.index')->with('success', 'Xóa điều hòa thành công!');
    }
}