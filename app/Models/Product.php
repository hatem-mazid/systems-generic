<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Illuminate\Database\Eloquent\Model;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'type',
        'price',
        'price_per_hour',
        'is_limited',
        'stock_quantity',
        'is_available'
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
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
