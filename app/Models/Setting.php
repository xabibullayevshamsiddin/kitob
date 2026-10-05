<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    /**
     * Get a setting by key, cached forever (invalidated on update).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::rememberForever("app_setting_{$key}", function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                if (!$setting) {
                    return $default;
                }
                return static::castValue($setting->value, $setting->type);
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $type = null): static
    {
        if ($type === null) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_array($value) => 'json',
                default => 'string',
            };
        }

        $storedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'group' => $group, 'type' => $type]
        );

        Cache::forget("app_setting_{$key}");

        return $setting;
    }

    /**
     * Get all settings grouped by group name.
     */
    public static function getAllGrouped(): array
    {
        $all = static::all();
        $grouped = [];

        foreach ($all as $item) {
            $grouped[$item->group][$item->key] = static::castValue($item->value, $item->type);
        }

        return $grouped;
    }

    /**
     * Cast stored string value to native PHP type.
     */
    protected static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    protected static function booted(): void
    {
        static::saved(function ($setting) {
            Cache::forget("app_setting_{$setting->key}");
        });

        static::deleted(function ($setting) {
            Cache::forget("app_setting_{$setting->key}");
        });
    }
}
