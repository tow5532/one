<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyInfoUserRolling extends Model
{
    protected $table = 'daily_info_rolling_user';

    protected $casts = [
        'user_arr' => 'array'
    ];
}
