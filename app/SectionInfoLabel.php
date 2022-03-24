<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SectionInfoLabel extends Model
{
    protected $table = 'section_info_label';
    protected $fillable = [
    ];
    public function getSection() {
        return $this->belongsTo('App\Section', 'section');
    }
    public function getSectionInfo() {
        return $this->belongsTo('App\SectionInfo', 'section_info');
    }
    protected $casts = [
        'created_at' => 'date:d-m-Y',
        'updated_at' => 'date:d-m-Y'
    ];

}
