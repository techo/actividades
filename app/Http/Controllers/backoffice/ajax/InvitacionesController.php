<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Http\Controllers\Controller;
use App\Jobs\EnviarInvitacionActividad;
use App\Pais;
use Illuminate\Http\Request;

/**
 * Endpoints JSON de la pantalla "Invitar a actividad" (push).
 *
 * Guardas de seguridad centrales:
 *  - El país objetivo se valida SIEMPRE en el servidor contra el conjunto de países
 *    que el admin puede alcanzar (idPaisPermitido; admin global => todos). Nunca se
 *    confía en el idPais que manda el cliente.
 *  - No se exporta ni devuelve PII de personas: los endpoints solo devuelven países,
 *    actividades y CONTEOS de destinatarios. El envío es in-app (push), sin dump.
 */
class InvitacionesController extends Controller
{
    /**
     * Países que este admin puede tomar como destino.
     * - Admin con país asignado (idPaisPermitido): solo ese país.
     * - Admin global (idPaisPermitido vacío): todos los países.
     *
     * Nota: no se reutiliza PaisesController@paisesPropios porque ese devuelve
     * lista vacía para el admin global (where id = null), que es justamente quien
     * coordina una respuesta multi-país.
     */
    public function paises()
    {
        $query = Pais::orderBy('nombre');

        $permitido = auth()->user()->idPaisPermitido;
        if (!empty($permitido)) {
            $query->where('id', $permitido);
        }

        return response()->json($query->get(['id', 'nombre']));
    }

    /**
     * Actividades elegibles para invitar. Se acotan a los países seleccionados
     * (que ya se filtran contra los permitidos) y, además, el global scope
     * BelongsToCountry las restringe al país del admin cuando corresponde.
     */
    public function actividades(Request $request)
    {
        $idsPaises = $this->paisesAutorizados((array) $request->input('idsPaises', []));

        $query = Actividad::orderBy('fechaInicio', 'desc');

        if (!empty($idsPaises)) {
            $query->whereIn('idPais', $idsPaises);
        }

        return response()->json(
            $query->limit(300)->get(['idActividad', 'nombreActividad', 'idPais', 'fechaInicio'])
        );
    }

    /**
     * Cuenta cuántas personas recibirían la invitación con el criterio elegido,
     * para mostrarlo antes de enviar (transparencia). Mismo criterio exacto que
     * el envío real (EnviarInvitacionActividad::segmento).
     */
    public function preview(Request $request)
    {
        $data = $this->validar($request, false);

        $destinatarios = EnviarInvitacionActividad::segmento(
            $data['idsPaises'], $data['segmento'], $data['canal']
        )->count();

        return response()->json(['destinatarios' => $destinatarios]);
    }

    /**
     * Valida, cuenta y despacha el job de envío. Devuelve el conteo para feedback.
     */
    public function enviar(Request $request)
    {
        $data = $this->validar($request, true);

        // La actividad debe existir y ser visible para el admin (el global scope
        // de país aplica acá al haber usuario autenticado). Si no, 404.
        $actividad = Actividad::find($data['idActividad']);
        if (!$actividad) {
            abort(404);
        }

        $destinatarios = EnviarInvitacionActividad::segmento(
            $data['idsPaises'], $data['segmento'], $data['canal']
        )->count();

        EnviarInvitacionActividad::dispatch(
            (int) $data['idActividad'],
            $data['idsPaises'],
            $data['segmento'],
            $data['titulo'],
            $data['mensaje'],
            auth()->user()->idPersona,
            $data['canal']
        );

        return response()->json([
            'ok'            => true,
            'destinatarios' => $destinatarios,
        ]);
    }

    /**
     * Valida el request y devuelve los datos ya saneados, con `idsPaises`
     * recortado al conjunto que el admin puede alcanzar. Si tras el recorte no
     * queda ningún país permitido, corta con 422.
     */
    private function validar(Request $request, bool $requiereActividad): array
    {
        $reglas = [
            'idsPaises'   => 'required|array|min:1',
            'idsPaises.*' => 'integer',
            'canal'       => 'required|in:' . implode(',', EnviarInvitacionActividad::CANALES),
            'segmento'    => 'required|in:' . implode(',', EnviarInvitacionActividad::SEGMENTOS),
        ];

        if ($requiereActividad) {
            // Los límites de longitud dependen del canal: push es corto por la plataforma
            // (65/240); email admite asunto largo y cuerpo HTML enriquecido. El tope de
            // 20000 deja margen holgado y entra en la columna `text` (65535 bytes) aun con
            // UTF-8 multibyte; las imágenes van por URL (filemanager), no embebidas.
            $esEmail    = $request->input('canal') === EnviarInvitacionActividad::CANAL_EMAIL;
            $maxTitulo  = $esEmail ? 150 : 65;
            $maxMensaje = $esEmail ? 20000 : 240;

            $reglas['idActividad'] = 'required|integer';
            $reglas['titulo']      = 'required|string|max:' . $maxTitulo;
            $reglas['mensaje']     = 'required|string|max:' . $maxMensaje;
        }

        $data = $request->validate($reglas);

        $data['idsPaises'] = $this->paisesAutorizados($data['idsPaises']);

        if (empty($data['idsPaises'])) {
            abort(response()->json([
                'message' => 'País no permitido',
                'errors'  => ['idsPaises' => ['No tenés permiso para enviar a los países seleccionados.']],
            ], 422));
        }

        return $data;
    }

    /**
     * Recorta una lista de ids de país al subconjunto que el admin puede alcanzar.
     * Admin global (idPaisPermitido vacío) puede alcanzar todos.
     *
     * @param  int[]|string[] $ids
     * @return int[]
     */
    private function paisesAutorizados(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        $permitido = auth()->user()->idPaisPermitido;
        if (empty($permitido)) {
            return $ids; // admin global
        }

        return array_values(array_intersect($ids, [(int) $permitido]));
    }
}
