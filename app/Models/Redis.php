<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redis extends Model
{
    public static function ClearArticles(){    	
		self::ClearBySearch('articles');
    }

    public static function ClearBySearch($s){
		$storage = Cache::getStore();
		$keys = $storage->getRedis()->keys('*');

		foreach ($keys as $key) {
			if (strpos($key, $s) !== false) {
			    $storage->getRedis()->del($key);
			}
			
		}

		// echo "Redis ".$s." cache Cleard".PHP_EOL;
    }
}
