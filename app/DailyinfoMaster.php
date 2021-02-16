<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyinfoMaster extends Model
{
    protected $table = 'daily_info_master';

    protected $fillable = [
        'search_date',
        'payment',
        'transfer' ,
        'balance' ,
        'regular' ,
        'bonus' ,
        'total' ,
        'normal_master_fee' ,
        'normal_company_fee' ,
        'normal_jack_fee' ,
        'sit_master_fee' ,
        'sit_company_fee' ,
        'tour_master_fee' ,
        'tour_company_fee' ,
        'company_chip_payment' ,
        'company_chip_reload' ,
        'company_chip_total' ,
        'user_chips' ,
        'user_safe' ,
        'user_deposit' ,
        'company_total_fee',
        'master_total_fee',
        'total_fee',
        'user_free_point',
        'game_item_money',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
