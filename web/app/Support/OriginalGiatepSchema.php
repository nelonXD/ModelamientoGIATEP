<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OriginalGiatepSchema
{
    private static ?bool $active = null;

    public static function isActive(): bool
    {
        return self::$active ??= DB::connection()->getDriverName() === 'mysql'
            && Schema::hasTable('USUARIO')
            && Schema::hasTable('CASO')
            && Schema::hasTable('DEPENDENCIA');
    }
}
