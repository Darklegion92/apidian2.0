<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailBlackList extends Model
{
    protected $table = 'email_black_list';

    protected $casts = [
        'banned' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'banned',
    ];
}
