<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    
    public function __construct(){
        $this->middleware('auth:api',[
            'except' => ['register', 'login', 'unauthorized']
        ]);
    }

    public function register(Request $request){
        $array = ['error' => false];

        $data = $request->only([
            'name',
            'email',
            'password'
        ]);

        $validator = $this->validator($data);

        if(!$validator->fails()){
            
            $name = $data['name'];
            $email = $data['email'];
            $password = $data['password'];

            $emailExits = User::where('email', $email)->count();
            if($emailExits === 0){
                //Salvar o usuário
                $hash = Hash::make($data['password']);

                $newUser = new User();
                $newUser->name = $name;
                $newUser->email = $email;
                $newUser->password = $hash;
                $newUser->save();

                //Token
                $token = auth()->attempt([
                    'email' => $email,
                    'password' => $password
                ]);

                if(!$token){
                    $array['error'] = 'Ocorreu um erro';
                    return $array;
                }

                //Avatar
                $info = auth()->user();
                $info['avatar'] = url('media/avatars/'. $info['avatar']);
                $array['data'] = $info;
                $array['token'] = $token;

            } else {
                $array['error'] = 'Email já cadastrado';
                return $array;
            }

        } else{
            $array['error'] = 'Dados incorretos';
            return $array;
        }

        return $array;
    }

    public function login(Request $request){
        $array = ['error' => false];

        $email = $request->input('email');
        $password = $request->input('password');

        $token = auth()->attempt([
            'email' => $email,
            'password' => $password
        ]);

        if(!$token){
            $array['error'] = 'Usuário e/ou Senha errados';
            return $array;
        }

        $info = auth()->user();
        $info['avatar'] = url('media/avatars/'. $info['avatar']);
        $array['avatar'] = $info['avatar'];
        $array['token'] = $token;

        return $array;
    }

    public function logout(Request $request){
        auth()->logout();
        return ['logout' => true];
    }

    public function refresh(Request $request){
        $array = ['error' => false];

        $token = auth()->refresh();
        $array['token'] = $token;

        return $array;
    }


    protected function validator(array $data){

        return Validator::make($data, [
            'name' => 'required',
            'email' => 'email|required|unique:users',
            'password' => 'required'
        ]);

    }
    
    public function unauthorized(){
        return response()->json([
            'error' => 'Não autorizado'
        ], 401);
    }
}
