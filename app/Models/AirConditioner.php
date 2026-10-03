<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirConditioner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'price',
        'description',
        'image',
        'origin',
        'warranty',
    ];

    public function images()
    {
        return $this->hasMany(AirConditionerImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class)->latest();
    }

    public function getPrimaryImagePathAttribute(): ?string
    {
        $galleryImage = $this->images()->first();

        if ($galleryImage) {
            return $galleryImage->image_path;
        }

        return $this->image;
    }

    // Quan hệ 1 Sản phẩm có nhiều Phân loại
    public function variants()
    {
        return $this->hasMany(AirConditionerVariant::class);
    }
}