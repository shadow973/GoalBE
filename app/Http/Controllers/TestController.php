<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TestController extends Controller
{
    public function index(){
    	echo "<pre>";
    	$storage = Cache::getStore();

    	// Cache::forget('laravel:test_key');
    	// $storage->getRedis()->flushAll();
    	$storage->getRedis()->del('laravel:test_key');

    	$keys = $storage->getRedis()->keys('*');

    	print_r($keys);
    	echo "<br>";
    	$res = Cache::remember('test_key', '1000', function () {
    		return 'res-'.time();
    	});

   
    	return $res;
    }
}
