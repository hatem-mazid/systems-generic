<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'order',
        'active',
    ];

    use InteractsWithMedia;

    public function products()
    {
        return $this->morphedByMany(Product::class, 'categorizable');
    }

    // Translations for this model (e.g. `description` by locale).
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    // Helper, similar to Product::t().
    public function t(string $key, string $locale = 'en')
    {
        $translation = $this->translations
            ->where('key', $key)
            ->where('locale', $locale)
            ->first();

        // Fallback to the base column value if no translation row exists.
        return $translation?->value ?? $this->{$key};
    }

    public function registerMediaCollections(): void
    {
        // Images for categories are stored in Spatie's `media` table.
        $this->addMediaCollection('image');
    }
}
