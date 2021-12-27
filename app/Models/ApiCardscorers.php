<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCardscorers extends Model
{
    protected $table = 'api_cardscorers';
    protected $connection =  LIVESCORE_CONNECTION;
}
