<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameLog extends Model
{
    protected $connection   = 'mssql_log';
    protected $table        = 'Web_UserPlayLog';
    protected $primaryKey   = 'Idx';


}
