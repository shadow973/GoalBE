<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiTeamsStandings extends Model
{
    //
    protected $connection =  LIVESCORE_CONNECTION;

    protected $hidden = ['stats'];
}
