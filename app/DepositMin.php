<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepositMin extends Model
{
    protected $table = 'deposits_min';

    protected $fillable = [
        'min_count',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
