<?php

namespace App\Http\Controllers;

use App\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index(){
        return Role::with('perms')
            ->get();
    }
}
