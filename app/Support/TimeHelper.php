<?php

namespace App\Support;

use Carbon\Carbon;

class TimeHelper
{
    public static function format(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return $value;
        }
    }
}
