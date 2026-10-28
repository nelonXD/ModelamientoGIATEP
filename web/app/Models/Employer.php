<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    protected $table = 'empresa_empleador';

    protected $primaryKey = 'Id_Empresa';

    public $timestamps = false;

    protected $guarded = [];
}
