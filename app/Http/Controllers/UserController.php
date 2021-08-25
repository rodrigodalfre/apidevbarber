<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Barber;
use App\Models\BarberServices;
use App\Models\UserFavorite;
use App\Models\UserAppointment;

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

    public function addFavorite(Request $request){
        $array = ['error' => ''];

        $id_barber = $request->input('barber');

        $barber = Barber::find($id_barber);

        if($barber){
            $favorite = UserFavorite::select()
                ->where('id_barber', $id_barber)
                ->where('id_user', $this->loggedUser->id)
            ->first();
                
            if(!$favorite){
                $newFav = new UserFavorite();
                $newFav->id_user = $this->loggedUser->id;
                $newFav->id_barber = $id_barber;
                $newFav->save();
                $array['favorited'] = true;
                
            } else{
                $favorite->delete();
                $array['favorited'] = false;
            }
        } else {
            $array['error'] = 'Barbeiro não encontrado';
        }
        return $array;
    }

    public function getFavorites(){
        $array = ['error' => ''];

        $favorites = UserFavorite::where('id_user', $this->loggedUser->id)->get();
        
        if($favorites){
            foreach($favorites as $fav){
                $barber = Barber::find($fav['id_barber']);
                $barber['avatar'] = url('media/avatars/'.$barber['avatar']);
                $array['list'][] = $barber;
            }
        }
        return $array;
    }

    public function getAppointments(){
        $array = ['error' => '', 'list' => []];

        $apps = UserAppointment::select()
            ->where('id_user', $this->loggedUser->id)
            ->orderBy('ap_datetime', 'DESC')
            ->get();

        if($apps) {
            foreach($apps as $app){
                $barber = Barber::find($app['id_barber']);
                $barber['avatar'] = url('media/avatars/'.$barber['avatar']);

                $service = BarberServices::find($app['id_service']);

                $array['list'][] = [
                    'id' => $app['id'],
                    'datetime' => $app['ap_datime'],
                    'barber' => $barber,
                    'service' => $service
                ];
            }
        } else {
            $array['error'] = 'Usuário não tem compromissos';
            return $array;
        }

        return $array;
    }

}
