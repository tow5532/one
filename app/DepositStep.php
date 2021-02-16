<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepositStep extends Model
{
    protected $table = 'deposits_step_category';

    protected $fillable = [
        'name',
        'code',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function depositsteps(){
        return $this->hasMany(Deposit::class);
    }
}
