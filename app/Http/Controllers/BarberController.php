<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

use App\Models\Barber;
use App\Models\BarberAvailability;
use App\Models\BarberFeedback;
use App\Models\BarberPhoto;
use App\Models\BarberServices;
use App\Models\User;
use App\Models\Appointment;
use App\Models\UserFavorite;

class BarberController extends Controller
{
    private $loggedUser;

    public function __construct(){
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    /*
    public function random(){
        $array = ['error' => ''];

        $names = ['Rodrigo', 'Miku', 'Brenda', 'Amanda', 'Leticia', 'Gabriel', 'Gabriela', 'Thais', 'Luiz', 'Diogo', 'José', 'Jeremias', 'Francisco', 'Dirce', 'Marcelo' ];
        $lastnames = ['Dalfré', 'Hatsune', 'Santos', 'Tesser', 'Alvaro', 'Sousa', 'Diniz', 'Josefa', 'Luiz', 'Diogo', 'Limoeiro', 'Santos', 'Limiro', 'Nazare', 'Mimoza' ];
        $services = ['Cortes', 'Pintura', 'Aparação', 'Unha', 'Progressiva', 'Limpeza de Pele', 'Corte feminino'];
        $services2 = ['Cortes', 'Pintura', 'Aparação', 'Unha', 'Progressiva', 'Limpeza de Pele', 'Corte feminino'];
        $comment = [
            'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
            'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
            'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
            'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
            'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.'
        ];

        for($q = 0; $q < 15; $q++){
            
            //Barber
            $newBarber = new Barber();
            $newBarber->name = $names[mt_rand(0, count($names) -1)] . ' ' . $lastnames[mt_rand(0, count($lastnames) -1)];
            $newBarber->avatar = mt_rand(1, 4).'.png';
            $newBarber->stars = mt_rand(0, 9) . '.' . mt_rand(0, 9);
            $newBarber->latitude = '-23.5'.mt_rand(0, 9).'30907';
            $newBarber->longitude = '-46.6'.mt_rand(0, 9).'82759';
            $newBarber->save();

            //Photos
            for($w = 0; $w < 4; $w++){
                $newBarberPhoto = new BarberPhoto();
                $newBarberPhoto->id_barber = $newBarber->id;
                $newBarberPhoto->url = mt_rand(1,5).'.png';
                $newBarberPhoto->save();
            }

            //Services
            $ns = mt_rand(3, 6);
            for($w = 0; $w < $ns; $w++){
                $newServices = new BarberServices();
                $newServices->id_barber = $newBarber->id;
                $newServices->name = $services[mt_rand(0, count($services) -1)]. ' de ' . $services2[mt_rand(0, count($services2) -1)];
                $newServices->price = mt_rand(0, 99). '.' . mt_rand(0, 99);
                $newServices->save();
            }
            
            //Feedback
            for($w = 0; $w < 3; $w++){
                $newbarberFeedback = new BarberFeedback();
                $newbarberFeedback->id_barber = $newBarber->id;
                $newbarberFeedback->name = $names[mt_rand(0, count($names) -1)];
                $newbarberFeedback->rate = mt_rand(1, 4). '.' . mt_rand(0, 9);
                $newbarberFeedback->body = $comment[mt_rand(0, count($comment) -1)];
                $newbarberFeedback->save();
            }

            //Availability
            for($e=0;$e<4;$e++){
                $rAdd = rand(7, 10);
                $hours = [];
                for($r=0;$r<8;$r++) {
                $time = $r + $rAdd;
                if($time < 10) {
                $time = '0'.$time;
            }
    
            $hours[] = $time.':00';
    
            }
            $newBarberAvail = new BarberAvailability();
                $newBarberAvail->id_barber = $newBarber->id;
                $newBarberAvail->weekday = $e;
                $newBarberAvail->hours = implode(',', $hours);
                $newBarberAvail->save();
            }
            
        }
        return $array;
    }
    */

    public function list () {
        $array = ['error' => ''];

        $barbers = Barber::all();
        $array['data'] = $barbers;

        foreach($barbers as $barber => $item){
            $barbers[$barber]['avatar'] = url('media/avatars/'.$barbers[$barber]['avatar']);
        }

        return $array;
    }

    public function one($id){
        $array = ['error' => ''];
        
        $barber = Barber::find($id);
        $idUser = $this->loggedUser->id;

        if($barber){

            $barber['avatar'] = url('media/avatars/'.$barber['avatar']);
            $barber['favorited'] = false;
            $barber['photos'] = [];
            $barber['services'] = [];
            $barber['feedback'] = [];
            $barber['available'] = [];

            //Favorite
            $favorite = UserFavorite::where('id_user', $idUser)
                ->where('id_barber', $barber)
                ->count();

            $favorite > 0 ? true : false;

            //Services
            $barber['services'] = BarberServices::select(['id', 'name', 'price'])
                ->where('id_barber', $barber->id)
            ->get();

            //Feedback
            $barber['feedback'] = BarberFeedback::select(['id', 'name', 'rate', 'body'])
                ->where('id_barber', $barber->id)
            ->get();

            //Photos
            $barber['photos'] = BarberPhoto::select(['id', 'url'])
                ->where('id_barber', $barber->id)
            ->get();

            foreach($barber['photos'] as $bpkey => $bkvalue) {
                $barber['photos'][$bpkey]['url'] = url('media/photos/'.$barber['photos'][$bpkey]['url']);
            }
            
            $array['data'] = $barber;

        } else {
            $array['error'] = 'Barbeiro não encontrado';
            return $array;
        }


        return $array;
    }

}
