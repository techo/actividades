<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;

/**
 * Pantalla de backoffice para que un admin componga y envíe una invitación push
 * a una actividad puntual, segmentada por país y por rol (coordinadores).
 *
 * Solo renderiza la vista; toda la lógica de segmentación y envío vive en
 * backoffice\ajax\InvitacionesController y en el job EnviarInvitacionActividad.
 */
class InvitacionesController extends Controller
{
    public function index()
    {
        return view('backoffice.invitaciones.index');
    }
}
