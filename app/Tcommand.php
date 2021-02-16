<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tcommand extends Model
{
    protected $connection = 'mssql';
    protected $table = 't_Command';

    protected $fillable = [
        'AccountUniqueID',
        'command',
        'val1',
        'val2',
    ];
}
