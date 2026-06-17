<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'label',
        'group',
        'description',
    ];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    public static function set(string $key, $value, string $label = null, string $group = 'general', string $description = null): void
    {
        $type = gettype($value);

        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'type' => $type,
                'label' => $label ?? $key,
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget("setting_{$key}");
    }

    public static function castValue($value, string $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'float', 'double', 'decimal' => (float) $value,
            'boolean' => (bool) $value,
            'array', 'json' => json_decode($value, true),
            default => $value,
        };
    }

    public static function getAdminFee(): float
    {
        return (float) self::get('admin_fee', 5000);
    }

    public static function setAdminFee(float $amount): void
    {
        self::set('admin_fee', $amount, 'Admin Fee', 'financial', 'Biaya admin tetap per transaksi');
    }
}
