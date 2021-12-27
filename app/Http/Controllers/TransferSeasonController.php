<?php

namespace App\Http\Controllers;

use App\Models\Transfers\Transfer;
use App\Models\Transfers\TransferSeason;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;

class TransferSeasonController extends Controller
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
        $perPage = $request->per_page ?? 25;

        return TransferSeason::paginate($perPage);
    }

    public function store(Request $request){
        if(!$this->user || !$this->user->hasRole('admin')){
            abort(401);
        }

        return TransferSeason::create($request->all());
    }

    public function show($id){
        return TransferSeason::findOrFail($id);
    }

    public function update($id, Request $request){
        if(!$this->user || !$this->user->hasRole('admin')){
            abort(401);
        }

        $transferSeason = TransferSeason::findOrFail($id);
        $transferSeason->update($request->all());

        return $transferSeason;
    }

    public function destroy($id){
        if(!$this->user || !$this->user->hasRole('admin')){
            abort(401);
        }

        TransferSeason::findOrFail($id)
            ->delete();
    }
}
