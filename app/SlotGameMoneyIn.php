<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlotGameMoneyIn extends Model
{
    protected $connection   = 'mssql_user';
    protected $table        = 'Web_UserMoneyIn';
    protected $primaryKey   = 'Idx';

    public $timestamps = false;
}
