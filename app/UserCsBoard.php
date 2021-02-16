<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCsBoard extends Model
{
    protected $table = 'user_cs_board';
    protected $primaryKey = 'usercsidx';

    protected $fillable = [
        'usercsidx',
        'username',
        'title',
        'contents',
        'regdate',
        'updatedate',
        'delete',
        'ans_title',
        'ans_contents',
        'ans_regdate',
    ];

    public $timestamps = false;
    const CREATED_AT = 'regdate';
    const UPDATED_AT = 'updatedate';
}
