<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'admin_roles';

    protected $fillable = [
        'id',
        'name',
        'slug'
    ];


    const CREATED_AT = 'regdate';
    const UPDATED_AT = 'updatedate';
}
