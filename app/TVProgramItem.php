<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TVProgramItem extends Model
{
    protected $table = 'tv_program_items';

    protected $fillable = [
        'tv_program_id',
        'time',
        'title',
        'created_at',
        'updated_at',
    ];
}
