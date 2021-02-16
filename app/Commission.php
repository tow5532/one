<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $table = 'commission';

    protected $fillable = [
        'user_id',
        'rolling',
        'losing',
        'company_yn',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
