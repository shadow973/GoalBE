<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use JWTAuth;

class ParsedArticlesController extends Controller
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        try {
            $this->user = JWTAuth::parseToken()->toUser();
        } catch (\Exception $e) {
        }
    }

    public function index(){
        $listing = \App\Models\ParsedArticles::where('status_id',1)
            ->orderBy('id','desc');

        return $listing->paginate(30);
    }

    public function detail(){
        $data = \App\Models\ParsedArticles::find($this->request->id);
        return $data;
    }
}
