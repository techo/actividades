<?php
namespace App\Payments;


use App\Actividad;
use App\Persona;
use App\Inscripcion;
use Illuminate\Http\Request;

class PayU implements PaymentGateway
{
    public $request;
    public $actividad;
    public $persona;
    public $inscripcion;

    public function __construct(Request $request)
    {
        $this->request = $request;
        list($idActividad, $idPersona, $idInscripcion) = explode('|', $request->referenceCode);
        //ToDo: Atrapar caso cuando alguno de los valores no existe
        $this->actividad = Actividad::findOrFail($idActividad);
        $this->persona = Persona::findOrFail($idPersona);
        $this->inscripcion = Inscripcion::findOrFail($idInscripcion);
    }

    public function success()
    {
        return $this->request->lapResponseCode === 'APPROVED';
    }

    public function error()
    {
        return $this->request->lapResponseCode !== 'APPROVED';
    }

    public function message()
    {
        return config('payments.payu.messages.' . $this->request->lapResponseCode);
    }
}