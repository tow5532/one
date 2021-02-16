<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roleuser extends Model
{
    protected $table = 'admin_role_users';

    protected $fillable = [
        'role_id',
        'user_id'
    ];


    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
