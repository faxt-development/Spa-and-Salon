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
            // First try to get the default theme
            $theme = Theme::where('is_default', true)->first();
            
            // If no default theme exists, create one
            if (!$theme) {
                $theme = Theme::create([
                    'name' => 'Default Theme',
                    'primary_color' => '#8B9259',
                    'secondary_color' => '#EDDFC0',
                    'accent_color' => '#8B5CF6',
                    'text_color' => '#1E293B',
                    'is_default' => true,
                ]);
            }
            
            return $theme;
        });
    }
}
