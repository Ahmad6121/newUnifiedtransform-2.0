<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class ColumnHelper
{
    public static function firstExisting(string $table, array $candidates, string $fallback = 'id'): string
    {
        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) {
                return $col;
            }
        }
        return $fallback;
    }

    public static function value($model, string $col, string $fallbackCol = 'id')
    {
        if ($model && isset($model->{$col})) return $model->{$col};
        if ($model && isset($model->{$fallbackCol})) return $model->{$fallbackCol};
        return null;
    }
}
