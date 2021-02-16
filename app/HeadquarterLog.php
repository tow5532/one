<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HeadquarterLog extends Model
{
    protected $table = 'headquarters_log';

    protected $fillable = [
        'user_id',
        'head_id',
        'deposit_id',
        'po_content',
        'point',
        'use_point',
        'mb_point',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
