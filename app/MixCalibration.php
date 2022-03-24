<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MixCalibration extends Model
{
    protected $table = 'mix_calibration';
    protected $fillable = [];

    public function infos() {
        return $this->morphMany('App\MixCalibration', 'section');
    }

    public function getMixSite() {
        return $this->belongsTo('App\Website', 'website');
    }
    public function getMixUser() {
        return $this->belongsTo('App\User','user');
    }

    protected $casts = [
        'created_at' => 'date:d-m-Y',
        'updated_at' => 'date:d-m-Y'
    ];

}
