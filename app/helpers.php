<?php

use App\Models\Setting;

function custom_setting($key, $default = null)
{
    return cache()->remember("setting_$key", 3600, function () use ($key, $default) {
        return Setting::where('key', $key)->value('value') ?? $default;
    });
}
