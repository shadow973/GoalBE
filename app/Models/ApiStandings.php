<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiStandings extends Model
{
    protected $table = 'api_standings';
    protected $connection =  LIVESCORE_CONNECTION;
}
