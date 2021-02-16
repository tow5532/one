<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefundAdmin extends Model
{
    protected $table = 'refund_admin';

    protected $fillable = [
        'user_id',
        'admin_id',
        'step_id',
        'amount',
        'bank',
        'account',
        'holder',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function refundstep()
    {
        return $this->belongsTo(RefundStep::class, 'step_id');
    }
}
