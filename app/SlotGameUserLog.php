<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlotGameUserLog extends Model
{
    protected $connection   = 'mssql_log';
    protected $table        = 'Web_UserPlayLog';
    protected $primaryKey   = 'Idx';

    public $timestamps = false;
}
