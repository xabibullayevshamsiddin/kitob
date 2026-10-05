<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get / set a setting value from settings table or cache.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}
