<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'bank_account';

    protected $fillable = [
        'user_id',
        'bank',
        'account',
        'holder',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
