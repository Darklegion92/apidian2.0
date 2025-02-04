<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubTypeWorker extends Model
{
    protected $table = 'sub_type_workers';

    protected $fillable = [
        'name', 'code',
    ];
}
