<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Refundquote extends Model
{
    protected $table = 'refund_inquote';

    protected $fillable = [
        'amount',
        'price',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
