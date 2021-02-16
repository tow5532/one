<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'deposits';

    protected $fillable = [
        'user_id',
        'admin_id',
        'amount',
        'charge_amount',
        'quote',
        'bank',
        'account',
        'holder',
        'step_id',
        'header_info',
        'ip',
        'sender',
        'bonus_point',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function depositstep() {
        return $this->belongsTo(DepositStep::class, 'step_id');
    }
}
