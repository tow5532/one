<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recommends extends Model
{
    protected $table = 'recommends';

    protected $fillable = [
        'id',
        'recommend_id',
        'user_id',
        'step1_id',
        'step2_id',
        'step3_id',
        'step4_id',
        'step5_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
