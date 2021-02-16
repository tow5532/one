<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoneyLog extends Model
{
    protected $table = 'money_logs';

    protected $fillable = [
        'level',
        'user_id',
        'username',
        'search_date',
        'rev_amount',
        'chg_amount' ,
        'now_amount' ,
        'reason',
        'refund_id',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
