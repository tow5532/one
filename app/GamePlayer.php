<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
    protected $connection   = 'mssql_noname';
    protected $table        = 'Player';
    protected $primaryKey   = 'AccountUniqueid';
}
