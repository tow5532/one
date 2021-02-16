<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefundMin extends Model
{
    protected $table = 'refund_min';

    protected $fillable = [
        'min_count',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
