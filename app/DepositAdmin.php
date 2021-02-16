<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepositAdmin extends Model
{
    protected $table = 'deposit_admin';

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function depositstep() {
        return $this->belongsTo(DepositStep::class, 'step_id');
    }
}
