<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SectionInfo extends Model
{
    protected $table = 'section_info';
    protected $fillable = [
    ];

    public function sectionLabel() {
        return $this->hasMany('App\SectionLabel');
    }

    public function getSection() {
        return $this->belongsTo('App\Section', 'section');
    }

    public function infoLabels() {
        return $this->hasMany('App\SectionInfoLabel','info');
    }

    protected $casts = [
        'created_at' => 'date:d-m-Y',
        'updated_at' => 'date:d-m-Y'
    ];

}
