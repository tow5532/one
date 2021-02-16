<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameTourRank extends Model
{
    protected $connection   = 'mssql';
    protected $table        = 'Tournament_Rank';
    //protected $primaryKey   = 'idx';
}
