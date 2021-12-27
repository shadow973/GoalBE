<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoriesController extends Controller
{
    public function tabCategories()
    {
    	return [
    		[	
				"id"=> 1,
				"title"=> "ახალი ამბები"
			],
			[
				"id"=> 8,
				"title"=> "ქართული"
			],
			[
				"id"=> 100,
				"title"=> "ფეხბურთი"
			],
			[
				"id"=> 98,
				"title"=> "კალათბურთი"
			],
			[
				"id"=> 97,
				"title"=> "რაგბი"
			],
			[
				"id"=> 99,
				"title"=> "ჩოგბურთი"
			],
			[
				"id"=> 130,
				"title"=> "სხვა"
			]
		];
    }
}
