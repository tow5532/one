<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GamesitMember extends Model
{
    protected $connection   = 'mssql';
    protected $table        = 'Log_SitNGo_UserList';
    protected $primaryKey   = 'idx';
}
