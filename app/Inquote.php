<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inquote extends Model
{
    protected $table = 'deposits_inquote';

    protected $fillable = [
        'amount',
        'price',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
