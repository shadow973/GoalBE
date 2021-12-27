<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiAssistscorers extends Model
{
    protected $table = 'api_assistscorers';
    protected $connection =  LIVESCORE_CONNECTION;
}
