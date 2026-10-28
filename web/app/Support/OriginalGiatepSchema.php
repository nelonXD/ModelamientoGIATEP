<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OriginalGiatepSchema
{
    public static function isActive(): bool
    {
        return DB::connection()->getDriverName() === 'mysql'
            && Schema::hasTable('USUARIO')
            && Schema::hasTable('CASO')
            && Schema::hasTable('DEPENDENCIA');
    }
}
