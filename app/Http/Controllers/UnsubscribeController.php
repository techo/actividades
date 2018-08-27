<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnsubscribeConfirmationRequest;
use App\Persona;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\MessageBag;

class UnsubscribeController extends Controller
{
    public function view($uuid)
    {
        $persona = Persona::where('unsubscribe_token', $uuid)
            ->where('recibirMails', 1)
            ->firstOrFail();
        $token = $persona->unsubscribe_token;
        return view('unsubscribe.confirm', compact('token'));
    }

    public function confirm(UnsubscribeConfirmationRequest $request, $uuid)
    {
        try {
            $persona = Persona::where('unsubscribe_token', $uuid)
                ->where('mail', $request->mail)
                ->where('recibirMails', 1)
                ->firstOrFail();
        } catch (ModelNotFoundException $e){
            $errors = new MessageBag(['mail' => 'El correo electrónico es inválido']);
            $token = $uuid;
            return response()->view('unsubscribe.confirm', compact('uuid', 'errors', 'token'));
        }

        $persona->recibirMails = 0;
        $persona->save();
        $request->session()->flash('status', 'Usted fue desuscripto correctamente');
        return view('unsubscribe.bye');
    }

}
