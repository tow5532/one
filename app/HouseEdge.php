<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HouseEdge extends Model
{
    protected $connection   = 'mssql';
    protected $table        = 'House_Edge';
    protected $primaryKey   = 'idx';

    protected $casts = [
        'log_date' => 'datetime',
    ];
}
