<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApiStage extends Model
{
    protected $table = 'api_stages';
    protected $connection =  LIVESCORE_CONNECTION;
}
