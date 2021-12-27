<?php

namespace App\Http\Controllers;

use App\HomeCategory;
use Illuminate\Http\Request;
use JWTAuth;

class HomeCategoriesController extends Controller
{
    protected $user;

    public function __construct(){
        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }
    }

    public function index(){
        $homeCategories = HomeCategory::query()
            ->orderBy('order')
            ->orderBy('created_at', 'asc')
            ->with('category')
            ->get();

        foreach($homeCategories as $key => $homeCategory){
            $homeCategories[$key]->category->articles = $homeCategory->category->articlesLimited()->with('mainGalleryItem')->limit(10)->get();
            unset($homeCategories[$key]->category->articlesLimited);
        }


        return $homeCategories;
    }

    public function store(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('home_categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'category_id' => 'required|exists:categories,id',
            'color'       => 'min:4|max:7',
        ]);

        $homeCategory = new HomeCategory($request->all());
        $homeCategory->save();

        return $homeCategory;
    }

    public function show($homeCategoryId){
        return HomeCategory::findOrFail($homeCategoryId);
    }

    public function reorder(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('home_categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $categorys = HomeCategory::whereIn('id', array_keys($request->all()))->get();

        foreach($categorys as $category){
            foreach($request->all() as $key => $val){
                if($category->id == $key){
                    $category->order = $val;
                    $category->save();
                }
            }
        }
    }

    public function update($homeCategoryId, Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('home_categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'category_id' => 'required|exists:categories,id',
            'color'       => 'min:4|max:7',
        ]);

        $homeCategory = HomeCategory::findOrFail($homeCategoryId);
        $homeCategory->update($request->all());

        return HomeCategory::find($homeCategoryId);
    }

    public function destroy($homeCategoryId){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('home_categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $homeCategory = HomeCategory::findOrFail($homeCategoryId);
        $homeCategory->delete();
    }
}
