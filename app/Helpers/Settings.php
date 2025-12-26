<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
class Settings
{
    protected static $file = 'settings.json';

    public static function get($key, $default = null)
    {
        $settings = self::all();
        return $settings[$key] ?? $default;
    }

    public static function set($key, $value)
    {
        $settings = self::all();
        $settings[$key] = $value;
        self::save($settings);
    }

    public static function all()
    {
        if (!Storage::exists(self::$file)) {
            return [];
        }

        return json_decode(Storage::get(self::$file), true) ?? [];
    }

    protected static function save($settings)
    {
        Storage::put(self::$file, json_encode($settings, JSON_PRETTY_PRINT));
    }
}
