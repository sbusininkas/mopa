<?php

namespace App\Helpers;

class SchoolYearHelper
{
    /**
     * Get available school years (current year - 10 years back)
     * Returns array like ['2024-2025', '2023-2024', ..., '2014-2015']
     */
    public static function getAvailableYears(): array
    {
        $currentYear = (int) date('Y');
        $years = [];
        
        for ($i = 0; $i < 11; $i++) {
            $year = $currentYear - $i;
            $years[] = ($year - 1) . '-' . $year;
        }
        
        return $years;
    }

    /**
     * Get current school year
     * Returns string like '2024-2025'
     */
    public static function getCurrentYear(): string
    {
        $currentYear = (int) date('Y');
        return ($currentYear - 1) . '-' . $currentYear;
    }

    /**
     * Validate school year format
     */
    public static function isValidYear(string $year): bool
    {
        $validYears = self::getAvailableYears();
        return in_array($year, $validYears);
    }
}
