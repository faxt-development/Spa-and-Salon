<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to avoid issues with is_default constraint
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Create default theme using colors from Tailwind config
        Theme::create([
            'name' => 'Default Theme',
            'primary_color' => '#8B9259',     // Olive green from Tailwind config
            'secondary_color' => '#EDDFC0',   // Beige from Tailwind config
            'accent_color' => '#8B5CF6',     // Violet-500 (kept as it's a good accent)
            'text_color' => '#1E293B',       // Slate-800 (dark gray for good contrast)
            'is_default' => true,
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
