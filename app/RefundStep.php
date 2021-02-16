<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefundStep extends Model
{
    protected $table = 'refund_step_category';

    protected $fillable = [
        'name',
        'code',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function refundsteps()
    {
        return $this->hasMany(Refund::class);
    }
}
