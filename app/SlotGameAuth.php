<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlotGameAuth extends Model
{
    protected $connection   = 'mssql_user';
    protected $table        = 'Web_UserAuth';
    protected $primaryKey   = 'Aid';

    public $timestamps = false;
}
