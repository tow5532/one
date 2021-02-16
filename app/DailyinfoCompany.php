<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyinfoCompany extends Model
{
    protected $table = 'daily_info_company';

    protected $fillable = [
        'search_date',
        'total_payment',
        'total_refund' ,
        'company_chip_payment' ,
        'company_chip_reload' ,
        'company_chip_total' ,
        'user_chips' ,
        'user_safe' ,
        'user_deposit' ,
        'normal_company_fee' ,
        'tour_company_fee' ,
        'sit_company_fee' ,
        'company_rev' ,
        'normal_jack_fee',
        'normal_master_fee',
        'tour_master_fee',
        'sit_master_fee',
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

    public function dailyMaster()
    {
        return $this->hasOne(DailyinfoMaster::class, 'search_date', 'search_date');
    }
}
