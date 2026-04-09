<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'type',
        'price',
        'is_limited',
        'stock_quantity',
        'active',
        'section_id',
    ];

    protected $casts = [
        'type' => ProductType::class,
        'price' => 'decimal:2',
        'is_limited' => 'boolean',
        'stock_quantity' => 'integer',
        'active' => 'boolean',
        'section_id' => 'integer',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default');
    }

    // 🔥 Helper
    public function t($key, $locale = 'en')
    {
        $translation = $this->translations
            ->where('key', $key)
            ->where('locale', $locale)
            ->first();

        return $translation?->value ?? $this->$key;
    }
}
