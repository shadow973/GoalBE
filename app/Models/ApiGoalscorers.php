<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiGoalscorers extends Model
{
    protected $table = 'api_goalscorers';
    protected $connection =  LIVESCORE_CONNECTION;
}
