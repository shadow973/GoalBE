<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TVProgram extends Model
{
    protected $table = 'tv_programs';

    protected $fillable = [
        'channel_id',
        'date',
    ];

    public function items(){
        return $this->hasMany(TVProgramItem::class, 'tv_program_id')
            ->orderBy('time', 'asc');
    }
}
