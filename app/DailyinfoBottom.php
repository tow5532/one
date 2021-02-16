<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyinfoBottom extends Model
{
    protected $table = 'daily_info_bottom';

    protected $fillable = [
        'search_date',
        'level',
        'user_id',
        'username',
        'total_payment',
        'total_refund' ,
        'user_chips' ,
        'user_safe' ,
        'user_deposit' ,
        'rev' ,
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
