<?php

namespace App\Http\Controllers;

use App\Models\AirConditioner;
use App\Models\AirConditionerVariant;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AirConditionerController extends Controller
{
    private function syncProductGallery(AirConditioner $airConditioner, array $uploadedFiles = [], ?UploadedFile $legacyImage = null, bool $append = false): void
    {
        $paths = [];

        foreach ($uploadedFiles as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $paths[] = $file->store('products', 'public');
            }
        }

        if (empty($paths) && $legacyImage instanceof UploadedFile && $legacyImage->isValid()) {
            $paths[] = $legacyImage->store('products', 'public');
        }

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

        $airConditioners = $query->latest()->get();

        return view('air_conditioners.index', compact('airConditioners'));
    }

    public function create()
    {
        return view('air_conditioners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'price' => 'required|numeric',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.capacity_name' => 'required|string',
            'variants.*.price' => 'required|numeric',
            'variants.*.weight' => 'required|integer',
        ]);

        $productData = $request->only(['name', 'brand', 'price', 'description', 'origin', 'warranty']);

        $airConditioner = AirConditioner::create($productData);

        if ($request->hasFile('images') || $request->hasFile('image')) {
            $this->syncProductGallery($airConditioner, $request->file('images', []), $request->file('image'));
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

    public function update(Request $request, $id)
    {
        $airConditioner = AirConditioner::findOrFail($id);

        $request->validate([
            'name'                  => 'required|string|max:255',
            'brand'                 => 'required|string|max:255',
            'price'                 => 'required|numeric',
            'weight'                => 'nullable|integer|min:100',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images'                => 'nullable|array',
            'images.*'              => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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

        if ($request->hasFile('images') || $request->hasFile('image')) {
            $this->syncProductGallery($airConditioner, $request->file('images', []), $request->file('image'), true);
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

        return redirect()->route('air_conditioners.index')->with('success', 'Cập nhật điều hòa thành công!');
    }

    public function destroy($id)
    {
        $airConditioner = AirConditioner::findOrFail($id);

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