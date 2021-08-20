<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

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

    public function update(Request $request){
        $array = ['error' => false];

        $data = $request->only([
            'name',
            'email',
            'password',
            'password_confirm'
        ]);

        $validator = Validator::make($data, [
            'name' => 'min:2',
            'email' => 'unique:users|email',
            'password' => 'same:password_confirm',
            'password_confirm' => 'same:password'
        ]);

        if($validator->fails()){
            $array['error'] = $validator->messages();
            return $array;
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirm = $request->input('password_confirm');

        $user = User::find($this->loggedUser->id);

        if($name) {
            $user->name = $name;
        }
        if($email){
            $user->email = $email;
        }
        if($password && $password_confirm){
            $user->password = Hash::make($password);
        }
        
        $user->save();

        return $array;
    }

}
