<?php

if (!function_exists('color_mix')) {
    /**
     * Mix two colors with a given weight.
     *
     * @param string $color1 First color (hex)
     * @param string $color2 Second color (hex)
     * @param int $weight Weight percentage (0-100) of the first color
     * @return string
     */
    function color_mix(string $color1, string $color2, int $weight = 50): string
    {
        // Remove any '#' from the beginning of the color strings
        $color1 = ltrim($color1, '#');
        $color2 = ltrim($color2, '#');

        // Convert hex to RGB
        $color1 = sscanf($color1, '%02x%02x%02x');
        $color2 = sscanf($color2, '%02x%02x%02x');

        // Calculate the mixed color
        $mixed = [];
        for ($i = 0; $i < 3; $i++) {
            $mixed[] = round($color1[$i] * ($weight / 100) + $color2[$i] * ((100 - $weight) / 100));
        }

        // Convert back to hex
        return '#' . sprintf('%02x%02x%02x', $mixed[0], $mixed[1], $mixed[2]);
    }
}
