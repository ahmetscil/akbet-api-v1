<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'section';
    protected $fillable = [];

    public function infos() {
        return $this->morphMany('App\SectionInfo', 'section');
    }

    public function getSectionSite() {
        return $this->belongsTo('App\Website', 'website');
    }
    public function getSectionUser() {
        return $this->belongsTo('App\User','user');
    }

    public function sectionInfos() {
        return $this->hasMany('App\SectionInfo','section');
    }

    protected $casts = [
        'created_at' => 'date:d-m-Y',
        'updated_at' => 'date:d-m-Y'
    ];

}
