<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProfitChange extends Model
{
    protected $table = 'recommend_fee_change';

    protected $fillable = [
        'user_id',
        'profit',
        'chg_profit',
        'check_update',
        'kind_cd',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

}
