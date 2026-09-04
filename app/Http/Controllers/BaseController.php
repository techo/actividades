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

    /**
     * Igual que intentaEnviar pero ESCALONADO: para loops que mandan a muchos
     * destinatarios (evaluación, actualización), reparte el envío en el tiempo
     * (~mailing.batch_por_minuto mails/min según el índice del loop) para no
     * disparar una ráfaga que sature el relay. Los mails sueltos (confirmaciones)
     * deben seguir usando intentaEnviar (inmediato).
     *
     * @param int $indice posición dentro del loop (0,1,2,…) para calcular el delay.
     */
    public function intentaEnviarEscalonado(Mailable $mailable, Persona $persona, int $indice = 0)
    {
        if (!$persona->recibirMails) {
            \Log::info('Mail a: ' . $persona->mail . ' no enviado por no aceptar notificaciones.');
            return;
        }

        $porSegundo = max(1, intdiv((int) config('mailing.batch_por_minuto', 120), 60));
        $delay = 5 + intdiv($indice, $porSegundo);

        return Mail::to($persona->mail)->later(now()->addSeconds($delay), $mailable);
    }
}