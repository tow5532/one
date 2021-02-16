<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Amountdata extends Model
{
    protected $table = 'amount_data';

    protected $fillable = [
        'id',
        'username',
        'amount',
        'amount_ratio',
        'dataType',
        'regdate',
        'update_date',
    ];

    public $timestamps = false;
    const CREATED_AT = 'regdate';
    const UPDATED_AT = 'update_date';
}
