<?php

if (!function_exists('format_hours')) {
    /**
     * Converts decimal hours to Hh MMmins format
     * Example: 7.50 -> 7h 30mins
     */
    function format_hours($decimalHours)
    {
        $decimalHours = (float)$decimalHours;
        if ($decimalHours <= 0) {
            return '0h';
        }

        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);

        if ($hours == 0 && $minutes == 0) {
            return '0h';
        }
        
        if ($hours == 0) {
            return $minutes . 'mins';
        }
        
        if ($minutes == 0) {
            return $hours . 'h';
        }

        return $hours . 'h ' . $minutes . 'mins';
    }
}
