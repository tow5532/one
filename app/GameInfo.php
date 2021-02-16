<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameInfo extends Model
{
    protected $table = 'game_info';

    protected $fillable = [
        'id',
        'name',
        'code',
        'inquote',
        'outquote',
        'min_amount',
        'chip_rate',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
