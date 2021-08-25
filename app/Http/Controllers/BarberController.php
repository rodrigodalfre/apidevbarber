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
use App\Models\UserAppointment;

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

    private function searchGeo($address){
        $key = env('MAPS_KEY', null);

        $address = urlencode($address);

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'$key='.$key;
        //Biblioteca cURL

        $ch = curl_init(); #iniciar sessão
        curl_setopt($ch, CURLOPT_URL, $url); #setup sessão
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Receber resposta da requisição
        $res = curl_exec($ch);
        curl_close($ch);

        return json_decode($res, true);
    }

    public function list () {
        $array = ['error' => ''];

        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $city = $request->input('city');
        $offset = $request->input('offset');
        if(!$offset){
            $offset = 0;
        }
        
        if(!empty($city)){
            $res = $this->searchGeo($city);

            if(count($res['results']) > 0 ){
                $lat = $res['results'][0]['geometry']['location']['lat'];
                $lng = $res['results'][0]['geometry']['location']['lng'];
            }
        } elseif(!empty($lat) && !empty($lng)){
            $res = $this->searchGeo($lat.','.$lng);

            if(count($res['results']) > 0 ){
                $city = $res['results'][0]['formatted_address'];
            }
        } else {
            $lat = '-23.5630907';
            $lng = '-46.6682795';
            $city = 'São Paulo';
        }

        $barbers = Barber::select(Barber::raw('*, SQRT(
            POW(69.1 * (latitude - '.$lat.'), 2) +
            POW(69.1 * ('.$lng.' - longitude) * COS(latitude / 57.3), 2)) AS distance'))
            ->havingRaw('distance < ?', [10])
            ->orderBy('distance', 'ASC')
            ->offset($offset)
            ->limit(5)
            ->get();

        foreach($barbers as $bkey => $bvalue){
            $barbers[$bkey]['avatar'] = url('media/avatars/'.$barbers[$bkey]['avatar']);
        }

        $array['data'] = $barbers;
        $array['loc'] = 'São Paulo';
 
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
            
            //Available
            $available = [];
        
            // - Disponibilidade (explode)
            $avails = BarberAvailability::where('id_barber', $barber->id)->get();
            $availsWeekDays = [];
            foreach($avails as $item) {
                $availsWeekDays[$item['weekday']] = explode(',', $item['hours']);
            }

            // - Agendamentos próximos 20 dias
            $appointments = [];
            $appQuery = UserAppointment::where('id_barber', $barber->id)
                ->whereBetween('ap_datetime', [
                    date('Y-m-d').' 00:00:00',
                    date('Y-m-d', strtotime('+20 days')).' 23:59:59'
                ])
                ->get();

            foreach($appQuery as $appItem) {
                $appointments[] = $appItem['ap_datetime'];
            }

            // - generate availability 
            for($q = 0; $q < 20; $q++){
                $timeItem = strtotime('+'.$q.' days');
                $weekDay = date('w', $timeItem);

                if(in_array($weekDay, array_keys($availsWeekDays))){
                    $hours = [];

                    $dayItem = date('Y-m-d', $timeItem);

                    foreach($availsWeekDays[$weekDay] as $hoursItem) {
                        $dayFormated = $dayItem . ' ' . $hoursItem . ':00';
                        if(!in_array($dayFormated, $appointments)) {
                            $hours[] = $hoursItem;
                        }
                    }

                    if(count($hours) > 0) {
                        $availability[] = [
                            'date' => $dayItem,
                            'hours' => $hours
                        ];
                    }
                }
            }

            $barber['available'] = $availability;
            $array['data'] = $barber;

        } else {
            $array['error'] = 'Barbeiro não encontrado';
            return $array;
        }


        return $array;
    }

    public function setAppointment(Request $request, $id) {
        $array = ['error' => ''];
        $service = $request->input('service');
        $year = intval($request->input('year'));
        $month = intval($request->input('month'));
        $day = intval($request->input('day'));
        $hour = intval($request->input('hour'));
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
        $hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
        //verificar se o servico do barbeiro existe
        $barberservice = BarberServices::where('id', $service)
            ->where('id_barber', $id)
            ->first();
            if(!$barberservice) {
                $array['error'] = 'Serviço inexistente';
                return $array;
            }
            
        //verificar se a data é real
        $apDate = $year.'-'.$month.'-'.$day.' '.$hour.':00:00';
        #echo $apDate;
        if(!strtotime($apDate) > 0) {
            $array['error'] = 'Data invalida!';
            return $array;
        }
        //vefificar se o barbeiro ja possui agendamento nesta data/hora
        $apps = UserAppointment::where('id_barber', $id)
            ->where('ap_datetime', $apDate)
            ->get();
        if(count($apps) > 0) {
            $array['error'] = 'Horário/dia indisponivel!';
            return $array;
        }
        //verificar se o barbeiro atende nesta data
        $weekDay = date('w', strtotime($apDate));
        $avail = BarberAvailability::select()
            ->where('id_barber', $id)
            ->where('weekday', $weekDay)
            ->first();
            if(!$avail) {
                $array['error'] = 'Barbeiro não atende neste dia!';
                return $array;
            }
        //Verificar se o barbeiro atende nesta hora
        $hours = explode(',', $avail['hours']);
        if(!in_array($hour.':00', $hours)){
            $array['error'] = 'Barbeiro não atende nesta hora!';
            return $array;
        }
        //fazer o agendamento
        $newApp = new UserAppointment();
        $newApp->id_user = $this->loggedUser->id;
        $newApp->id_barber = $id;
        $newApp->id_service = $service;
        $newApp->ap_datetime = $apDate;
        $newApp->save();
        return $array;
    }

    public function search(Request $request){
        $array = ['error' => '', 'list' => []];

        $q = $request->input('q');

        if($q){
            $barbers = Barber::select()
                ->where('name', 'LIKE', '%'.$q.'%')
            ->get();

            foreach($barbers as $bkey => $barber){
                $barbers[$bkey]['avatar'] = url('media/avatars/'.$barbers[$bkey]['avatar']);
            }

            $array['list'] = $barbers;

        } else {
            $array['error'] = "Digite algo para buscar";
        }

        return $array;
    }

}
