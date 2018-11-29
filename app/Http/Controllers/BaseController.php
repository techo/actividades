<?php
namespace App\Http\Controllers;

use App\Persona;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
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

    public function intentaEnviar(PendingMail $mail, Mailable $mailable, Persona $persona = null)
    {
        $persona = $persona == null ? Auth::user() : $persona;

        if($persona->recibirMails){
          \Log::info('Mail en cola para '. $persona->mail .' con.');
          return $mail->queue($mailable);
        }

        \Log::info('Mail a: ' . Auth::user()->mail . ' no enviado por no aceptar notificaciones.');
    }
}