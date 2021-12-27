<?php

namespace App\Models\Transfers;

use Illuminate\Database\Eloquent\Model;

class TransferSeason extends Model
{
    protected $fillable = ['name'];

    public function transfers() {
        return $this->hasMany(Transfer::class, 'transfer_season_id');
    }
}
