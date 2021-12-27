<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Intervention;
use Storage;
use JWTAuth;

class CategoriesController extends Controller
{
    protected $user;


    public function __construct(){
        try{
            $this->user = JWTAuth::parseToken()->toUser();
        }
        catch(\Exception $e){

        }


    }

    public function index(Request $request){

        $categories = Category::where('is_visible', true)
                            // ->withCount('articles', 'users')
                            ;

        if($request->has('withHidden')){
            $categories->orWhere('is_visible', false);
        }

        return $categories
            ->defaultOrder()
            ->withCount('userSubscriptions')
            ->get()
            ->toTree();
    }

    public function store(Request $request){

        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'title'     => 'required',
            // 'parent_id' => 'exists:categories,id',
        ]);

        // dd($category->toArray());

        $category = new Category($request->all());

        if($request->has('image')){
            die('img');
            $image = Intervention::make($request->get('image'));
            $imageName = str_random(32);

            $image = $image->fit(32, 32)
                ->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/categories/' . $imageName . '.png',
                (string)$image
            );

            $category->image = 'images/categories/' . $imageName . '.png';
        }

        if($request->has('background_image')){
            $backgroundImage = Intervention::make($request->get('background_image'));
            $backgroundImageName = str_random(32);

            $backgroundImage = $backgroundImage->fit(1920, 200)
                ->encode('jpg', 90)
                ->stream();

            Storage::disk('sftp')->put(
                'images/category_backgrounds/' . $backgroundImageName . '.jpg',
                (string)$backgroundImage
            );

            $category->background_image = 'images/category_backgrounds/' . $backgroundImageName . '.jpg';
        }

        // if(!isset($data['parent_id']) || empty($data['parent_id'])){
        //     $category->parent_id = null;
        // }
        if(!isset($data['parent_id']) || empty($data['parent_id'])){
            $category->parent_id = null;
        }

        if(isset($request->parent_id)){
            $category->parent_id = $request->parent_id;
        }


        $category->slug = url_slug($category->title,['transliterate' => true]);
        $category->save();

        return Category::find($category->id);
    }

    public function show($categoryId, Request $request){
//        die('asd');
        $category = Category::findOrFail($categoryId)
            ->descendantsAndSelf($categoryId)
            ->toTree()[0];

        if(isset($request->single)){
            $res = $category->toArray();

            return response()
                ->json( $res );
        }


        $category->articles = $category->articles();

        if(str_contains($request->get('options'), 'news-only')){
            $category->articles->whereHas('mainGalleryItem', function($q){
                $q->where('type', 'image');
            });
        }

        if(str_contains($request->get('options'), 'video')){
            $category->articles->whereHas('mainGalleryItem', function($q){
                $q->where('type', 'video');
            });
        }

        if(str_contains($request->get('options'), 'paginate')){
            $category->articles = $category->articles->paginate(30);
            $category->articles->load('mainGalleryItem');
            return $category;
        }
        else{
            return $category->load('articles.mainGalleryItem');
        }
    }

    public function update($categoryId, Request $request){
        
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }



        $this->validate($request, [
            'title'     => '',
            'image_new' => '',
            // 'parent_id' => 'exists:categories,id',
        ]);


        $category = Category::findOrFail($categoryId);
        $data = $request->all();

        $category->descendants()->update(['is_visible' => $request->get('is_visible')]);

        if(!isset($data['parent_id'])){
            $data['parent_id'] = null;
        }

        if(!isset($data['image_new'])){
            unset($data['image']);
        }

        if(!isset($data['background_image_new'])){
            unset($data['background_image']);
        }

        if($request->get('delete_image')){
            $data['image'] = NULL;
        }

        if($request->get('delete_background_image')){
            $data['background_image'] = NULL;
        }

        if(isset($data['image_new'])){
            $image = Intervention::make($data['image_new']);
            $imageName = str_random(32);

            $image = $image->fit(100, 100)
                ->encode('png')
                ->stream();

            Storage::disk('sftp')->put(
                'images/categories/' . $imageName . '.png',
                (string)$image
            );

            $data['image'] = 'images/categories/' . $imageName . '.png';

            $category->deleteImage();
            unset($data['image_new']);
        }

        if(isset($data['background_image_new'])){

            $backgroundImage = Intervention::make($data['background_image_new']);
            $backgroundImageName = str_random(32);

            $backgroundImage = $backgroundImage->fit(1920, 200)
                ->encode('jpg', 90)
                ->stream();

            Storage::disk('sftp')->put(
                'images/category_backgrounds/' . $backgroundImageName . '.jpg',
                (string)$backgroundImage
            );

            $data['background_image'] = 'images/category_backgrounds/' . $backgroundImageName . '.jpg';

            $category->deleteBackgroundImage();
            unset($data['background_image_new']);
        }

        // return response()->json($data);

        unset($data['avatar']);
        unset($data['_method']);

        // print_r($data);
        // die;

        // $category->title = 'ევროპის ლიგა';
        // $category->save();
        $data['slug'] = url_slug($data['title'],['transliterate' => true]);
        $category->update($data);

        return Category::find($categoryId);
    }

    public function destroy($categoryId){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $category = Category::findOrFail($categoryId);
        $category->delete();
    }

    public function reorder(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('categories_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        Category::rebuildTree(
            json_decode($request->get('json'), true),
            false
        );
    }

    public function export(Request $request)
    {
        $categories = Category::select('title')
            ->orderBy('created_at')
            ->withCount('articles', 'users')
            ->get();

        Excel::create('categories', function ($excel) use ($categories) {
            $excel->sheet('Sheet 1', function ($sheet) use ($categories) {
                $sheet->fromArray($categories);
            });
        })->export('xls', [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
