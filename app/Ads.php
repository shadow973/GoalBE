<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{

    public $appends = ['position_slug'];

    public function position() {
        return $this->belongsTo(AdsPosition::class);
    }

    public function getPositionSlugAttribute() {
        return $this->position()->count() ? $this->position()->first()->slug : null;
    }
}
