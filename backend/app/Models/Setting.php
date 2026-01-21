<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a setting value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('user_id', auth()->id())
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return $default;
        }

        // Try to decode as JSON if it looks like JSON
        if (is_string($setting->value) && in_array($setting->value[0] ?? null, ['{', '['])) {
            return json_decode($setting->value, true);
        }

        return $setting->value;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value): void
    {
        $value = is_array($value) ? json_encode($value) : $value;

        self::updateOrCreate(
            ['user_id' => auth()->id(), 'key' => $key],
            ['value' => $value]
        );
    }
}
