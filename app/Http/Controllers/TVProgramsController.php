<?php

namespace App\Http\Controllers;

use App\TVProgram;
use App\TVProgramItem;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use JWTAuth;

class TVProgramsController extends Controller
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
        $tvPrograms = TVProgram::orderBy('date', 'desc')
            ->orderBy('channel_id')
            ->with('items');

        if($request->has('channel_id')){
            $tvPrograms->where('channel_id', $request->get('channel_id'));
        }

        if($request->has('date')){
            $tvPrograms->where('date', $request->get('date'));
        }

        return $tvPrograms->get();
    }

    public function store(Request $request){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('tv_programs_crud') && !$this->user->hasRole('admin')) {
            abort(403);
        }

        $this->validate($request, [
            'channel_id' => 'required|in:1,2|unique_with:tv_programs,date',
            'date'       => 'required|date',
            'file'       => 'required',
        ]);

        $tvProgram = new TVProgram($request->all());
        $tvProgram->save();

        if($request->hasFile('file')){
            $data = Excel::load($request->file('file'), null)->get();

            if(!empty($data) && $data->count()){
                $data = array_map('array_values', $data->toArray());
                foreach($data as $row){
                    $recordsToInsert[] = [
                        'tv_program_id' => $tvProgram->id,
                        'time'          => (new \DateTime($row[0]))->format('H:i'),
                        'title'         => $row[1],
                        'created_at'    => $tvProgram->created_at,
                        'updated_at'    => $tvProgram->updated_at,
                    ];
                }

                if(!empty($recordsToInsert)){
                    TVProgramItem::insert($recordsToInsert);
                }
            }
        }

        return $tvProgram;
    }

    public function show($id){
        $tvProgram = TVProgram::findOrFail($id);
        return $tvProgram;
    }

    public function update(Request $request, $tvProgramId){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('tv_programs_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $this->validate($request, [
            'channel_id' => "required|in:1,2|unique_with:tv_programs,date,{$tvProgramId}",
            'date'       => 'required|date',
        ]);

        $tvProgram = TVProgram::findOrFail($tvProgramId);
        $tvProgram->update($request->all());

        if($request->hasFile('file')){
            $data = Excel::load($request->file('file'), null)->get();

            if(!empty($data) && $data->count()){
                $data = array_map('array_values', $data->toArray());
                foreach($data as $row){
                    $recordsToInsert[] = [
                        'tv_program_id' => $tvProgramId,
                        'time' => $row[0],
                        'title' => $row[1],
                        'created_at' => $tvProgram->created_at,
                        'updated_at' => $tvProgram->updated_at
                    ];
                }

                if(!empty($recordsToInsert)){
                    $tvProgram->items()->delete();
                    TVProgramItem::insert($recordsToInsert);
                }
            }
        }

        return $tvProgram;
    }

    public function destroy($id){
        if(!$this->user){
            abort(401);
        }

        if(!$this->user->can('tv_programs_crud') && !$this->user->hasRole('admin')){
            abort(403);
        }

        $tvProgram = TVProgram::findOrFail($id);
        $tvProgram->delete();
    }
}
