<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    /**
     * Retrieve a setting's value securely with forever caching
     */
    public static function get(string $key, $default = null)
    {
        return cache()->rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set/update a setting's value and automatically flush the cache
     */
    public static function set(string $key, ?string $value, string $group = 'general'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
        cache()->forget("setting.{$key}");
    }

    /**
     * Flush all settings caches
     */
    public static function flushCache(): void
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            cache()->forget("setting.{$setting->key}");
        }
    }
}
