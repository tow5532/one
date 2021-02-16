<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogMoney extends Model
{
    protected $connection   = 'mssql';
    protected $table        = 'Log_Money';
    protected $primaryKey   = 'idx';

}
