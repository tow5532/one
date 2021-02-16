<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameTourRegist extends Model
{
    protected $connection   = 'mssql';
    protected $table        = 'tnmt_regist';
    //protected $primaryKey   = 'idx';
}
