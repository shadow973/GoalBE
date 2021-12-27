<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCountries extends Model
{
    protected $table = 'api_countries';
    protected $connection =  LIVESCORE_CONNECTION;
}
