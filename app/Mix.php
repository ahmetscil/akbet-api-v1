<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mix extends Model
{
    protected $table = 'mix';
    protected $fillable = [];

    public function allMixUsers() {
        return $this->belongsToMany('App\User', 'users');
    }
    public function getMixUser() {
        return $this->belongsTo('App\User','users');
    }

    protected $casts = [
        'created_at' => 'date:d-m-Y',
        'updated_at' => 'date:d-m-Y'
    ];


}
