<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NPrimaryKey extends Model
{
    protected $table="PRIMARYKEYS";
    protected $connection= "sqlsrv_proserla";
}
