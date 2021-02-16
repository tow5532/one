<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $table = 'points';

    protected $fillable = [
        'user_id',
        'deposit_id',
        'refund_id',
        'po_content',
        'point',
        'use_point',
        'mb_point',
        'created_at',
        'updated_at',
    ];

    /*protected $dates = [
        'created_at',
        'updated_at',
    ];*/


    public function user() {
        //return $this->belongsTo(User::class, 'user_id');
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
