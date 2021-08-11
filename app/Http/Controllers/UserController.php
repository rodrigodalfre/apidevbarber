<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    private $loggedUser;

    public function __construct(){
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function read(){
        $array = ['error' => false];

        $info = $this->loggedUser;
        $array['data'] = $info;
        $info['avatar'] = url('media/avatars/'.$info['avatar']);
        $array['avatar'] = $info['avatar'];

        return $array;
    }

    
}
