<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlotGameMoneyOut extends Model
{
    protected $connection   = 'mssql_user';
    protected $table        = 'Web_UserMoneyOut';
    protected $primaryKey   = 'Idx';

    public $timestamps = false;
}
