<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyinfoBottomTotal extends Model
{
    protected $table = 'daily_info_bottom_total';

    protected $fillable = [
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

    public function bottomlist()
    {
        return $this->hasMany(DailyinfoBottom::class, 'user_id', 'user_id');
    }

    public function moneyloglist()
    {
        return $this->hasMany(MoneyLog::class, 'user_id', 'user_id');
    }
}
