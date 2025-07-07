<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Theme;

class ThemeService
{
    public function getCurrentTheme()
    {
        if (auth()->check() && $company = auth()->user()->company) {
            return $company->theme ?? $this->getDefaultTheme();
        }

        return $this->getDefaultTheme();
    }

    public function getDefaultTheme()
    {
        return Cache::rememberForever('default_theme', function () {
            return Theme::where('is_default', true)->firstOrFail();
        });
    }
}
