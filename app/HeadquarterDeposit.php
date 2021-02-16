<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HeadquarterDeposit extends Model
{
    protected $table = 'headquarters_deposit';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
