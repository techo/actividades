<?php
namespace App\Http\Controllers;

use App\Persona;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    function paginate($items, $perPage, $parameters = [])
    {
        if(is_array($items)){
            $items = collect($items);
        }

        return new LengthAwarePaginator(
            $items->forPage(Paginator::resolveCurrentPage() , $perPage),
            $items->count(),
            $perPage,
            Paginator::resolveCurrentPage(),
            ['path' => Paginator::resolveCurrentPath(), 'query' => $parameters]
        );
    }

    public function intentaEnviar(Mailable $mailable, Persona $persona)
    {
        $mail = Mail::to($persona->mail);

        if($persona->recibirMails){
          \Log::info('Mail en cola para '. $persona->mail .' con.');
          return $mail->queue($mailable);
        }

        \Log::info('Mail a: ' . $persona->mail . ' no enviado por no aceptar notificaciones.');
    }
}