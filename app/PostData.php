<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostData extends Model
{
    protected $table = 'post_data';
    protected $fillable = [];

    protected $casts = [
        'created_at' => 'date:d-m-Y',
        'updated_at' => 'date:d-m-Y'
    ];
}
