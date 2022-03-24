<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';
    protected $fillable = [];

    public function allProjectUsers() {
        return $this->belongsToMany('App\User', 'users');
    }
    public function getProjectUser() {
        return $this->belongsTo('App\User','users');
    }

    protected $casts = [
        'created_at' => 'date:d-m-Y',
        'updated_at' => 'date:d-m-Y'
    ];

    public function getFullAddressAttribute() {
        return $this->address . ' - ' . $this->city;
    }

}
