<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Devices extends Model
{
    protected $table = 'devices';
    protected $fillable = [];


    public function getLastDataAttribute() {
        $now = Carbon ::now();
        $last = $this->last_data_at;
        $cur = $last -> diffInMinutes($now, false);
        if ($cur >= 30) {
            $res = '<p class="btn btn-warning btn-sm mb-0 rounded-0">Sleeping</p>';
        } else {
            $res = '<p class="btn btn-success btn-sm mb-0 rounded-0">'.$cur.'</p>';
        }
        return $res;
    }


    protected $casts = [
        'created_at' => 'date:d-m-Y',
        'updated_at' => 'date:d-m-Y',
        'last_data_at' => 'date:H:i:s'
    ];
}
