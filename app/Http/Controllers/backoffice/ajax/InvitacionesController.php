<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Campaign;
use App\Http\Controllers\Controller;
use App\Jobs\EnviarComunicacionCampania;
use App\Jobs\EnviarInvitacionActividad;
use App\Pais;
use App\Suscribe;
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
     * Campañas elegibles como objetivo, acotadas a los países autorizados. Campaign no
     * tiene global scope de país (a diferencia de Actividad) y usa `pais_id` (snake_case),
     * así que el filtro es explícito.
     */
    public function campanas(Request $request)
    {
        $idsPaises = $this->paisesAutorizados((array) $request->input('idsPaises', []));

        $query = Campaign::orderBy('created_at', 'desc');

        if (!empty($idsPaises)) {
            $query->whereIn('pais_id', $idsPaises);
        }

        return response()->json(
            $query->limit(300)->get(['id', 'nombre', 'pais_id', 'tipo', 'estado'])
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

        // Alcanzables por el canal/audiencia y tamaño total (ignorando el opt-in). La
        // diferencia son quienes no pueden recibir por este canal.
        list($alcanzables, $total) = $this->contar($data);

        return response()->json([
            'destinatarios' => $alcanzables,          // alcanzables (compat)
            'total'         => $total,                // tamaño total de la audiencia
            'sin_canal'     => max(0, $total - $alcanzables),
        ]);
    }

    /**
     * Cuenta [alcanzables, total] según el objetivo y la audiencia elegidos.
     *  - actividad: segmento de voluntarios por el canal (push/email).
     *  - campaña + audiencia 'segmento': segmento de voluntarios por email.
     *  - campaña + audiencia 'suscriptos': leads de la campaña con mail (alcanzables) vs todos.
     */
    private function contar(array $data): array
    {
        if ($data['objetivo'] === 'campania') {
            if ($data['audiencia'] === EnviarComunicacionCampania::AUDIENCIA_SEGMENTO) {
                $canal       = EnviarInvitacionActividad::CANAL_EMAIL;
                $alcanzables = EnviarInvitacionActividad::segmento($data['idsPaises'], $data['segmento'], $canal)->count();
                $total       = EnviarInvitacionActividad::segmento($data['idsPaises'], $data['segmento'], $canal, false)->count();
                return [$alcanzables, $total];
            }

            // Suscriptos de la campaña: alcanzables = con mail; total = todos los suscriptos.
            $idCampania  = (int) $data['idCampania'];
            $alcanzables = EnviarComunicacionCampania::suscriptosAlcanzables($idCampania)->count();
            $total       = Suscribe::where('campaign_id', $idCampania)->count();
            return [$alcanzables, $total];
        }

        // Objetivo actividad: segmento por el canal elegido.
        $alcanzables = EnviarInvitacionActividad::segmento($data['idsPaises'], $data['segmento'], $data['canal'])->count();
        $total       = EnviarInvitacionActividad::segmento($data['idsPaises'], $data['segmento'], $data['canal'], false)->count();
        return [$alcanzables, $total];
    }

    /**
     * Valida, cuenta y despacha el job de envío. Devuelve el conteo para feedback.
     */
    public function enviar(Request $request)
    {
        $data = $this->validar($request, true);

        list($destinatarios) = $this->contar($data);

        if ($data['objetivo'] === 'campania') {
            // La campaña debe existir y pertenecer a un país autorizado.
            $campaign = Campaign::find($data['idCampania']);
            if (!$campaign || ($campaign->pais_id && !in_array((int) $campaign->pais_id, $data['idsPaises'], true))) {
                abort(404);
            }

            EnviarComunicacionCampania::dispatch(
                (int) $data['idCampania'],
                $data['idsPaises'],
                $data['audiencia'],
                $data['audiencia'] === EnviarComunicacionCampania::AUDIENCIA_SEGMENTO ? $data['segmento'] : null,
                $data['titulo'],
                $data['mensaje'],
                auth()->user()->idPersona
            );

            return response()->json(['ok' => true, 'destinatarios' => $destinatarios]);
        }

        // Objetivo actividad: la actividad debe existir y ser visible para el admin (el
        // global scope de país aplica acá al haber usuario autenticado). Si no, 404.
        $actividad = Actividad::find($data['idActividad']);
        if (!$actividad) {
            abort(404);
        }

        EnviarInvitacionActividad::dispatch(
            (int) $data['idActividad'],
            $data['idsPaises'],
            $data['segmento'],
            $data['titulo'],
            $data['mensaje'],
            auth()->user()->idPersona,
            $data['canal']
        );

        return response()->json(['ok' => true, 'destinatarios' => $destinatarios]);
    }

    /**
     * Valida el request y devuelve los datos ya saneados, con `idsPaises`
     * recortado al conjunto que el admin puede alcanzar. Si tras el recorte no
     * queda ningún país permitido, corta con 422.
     */
    private function validar(Request $request, bool $requiereContenido): array
    {
        $objetivo = $request->input('objetivo', 'actividad');

        $reglas = [
            'objetivo'    => 'required|in:actividad,campania',
            'idsPaises'   => 'required|array|min:1',
            'idsPaises.*' => 'integer',
        ];

        if ($objetivo === 'campania') {
            // Campaña siempre por email (los leads no tienen dispositivo y la app aún no
            // puede abrir una campaña por deep link). Audiencia: suscriptos o un segmento.
            $reglas['idCampania'] = 'required|integer';
            $reglas['audiencia']  = 'required|in:' . implode(',', EnviarComunicacionCampania::AUDIENCIAS);
            if ($request->input('audiencia') === EnviarComunicacionCampania::AUDIENCIA_SEGMENTO) {
                $reglas['segmento'] = 'required|in:' . implode(',', EnviarInvitacionActividad::SEGMENTOS);
            }
            if ($requiereContenido) {
                $reglas['titulo']  = 'required|string|max:150';
                $reglas['mensaje'] = 'required|string|max:20000';
            }
        } else {
            $reglas['canal']    = 'required|in:' . implode(',', EnviarInvitacionActividad::CANALES);
            $reglas['segmento'] = 'required|in:' . implode(',', EnviarInvitacionActividad::SEGMENTOS);
            if ($requiereContenido) {
                // Límites por canal: push es corto por la plataforma (65/240); email admite
                // asunto largo y cuerpo HTML (20000 entra en la columna `text` aun multibyte;
                // las imágenes van por URL del filemanager, no embebidas).
                $esEmail = $request->input('canal') === EnviarInvitacionActividad::CANAL_EMAIL;
                $reglas['idActividad'] = 'required|integer';
                $reglas['titulo']      = 'required|string|max:' . ($esEmail ? 150 : 65);
                $reglas['mensaje']     = 'required|string|max:' . ($esEmail ? 20000 : 240);
            }
        }

        $data = $request->validate($reglas);
        $data['objetivo'] = $objetivo;

        // El canal para campaña se fija en el servidor (email), no se confía en el cliente.
        if ($objetivo === 'campania') {
            $data['canal'] = EnviarInvitacionActividad::CANAL_EMAIL;
        }

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
