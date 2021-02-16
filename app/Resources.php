<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resources extends Model
{
    protected $table = 'resources';

    protected $fillable = [
        'category',
        'title',
        'title_img',
        'content',
		'content_img',
        'content_link',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
