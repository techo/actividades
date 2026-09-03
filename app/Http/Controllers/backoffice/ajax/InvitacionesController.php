<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Campaign;
use App\Comunicacion;
use App\ComunicacionDestinatario;
use App\Http\Controllers\Controller;
use App\Jobs\EnviarComunicacionCampania;
use App\Jobs\EnviarInvitacionActividad;
use App\Pais;
use App\Services\MailThrottle;
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
     * Historial de comunicaciones enviadas + conversión. País-scope: admin global ve
     * todo; admin de país ve las que apuntaron a su país. Solo devuelve conteos y nombres,
     * nunca PII de destinatarios.
     */
    public function enviadas(Request $request)
    {
        $query = Comunicacion::with('admin')->orderBy('created_at', 'desc');

        $paisScope = auth()->user()->idPaisPermitido;
        if (!empty($paisScope)) {
            $query->whereRaw('JSON_CONTAINS(paises, ?)', [(string) (int) $paisScope]);
        }

        $page = $query->paginate(20);

        return response()->json([
            'data'         => collect($page->items())->map([$this, 'presentarComunicacion']),
            'current_page' => $page->currentPage(),
            'last_page'    => $page->lastPage(),
            'total'        => $page->total(),
        ]);
    }

    /** Arma la fila del historial para una comunicación (metadatos + conversión + %). */
    public function presentarComunicacion(Comunicacion $c): array
    {
        list($conversion, $conversionLabel, $conversionAplica) = $this->conversion($c);

        $destinatarios = (int) $c->destinatarios_count;
        $pct = ($conversionAplica && $destinatarios > 0) ? (int) round($conversion * 100 / $destinatarios) : null;

        return [
            'id'               => $c->id,
            'fecha'            => optional($c->created_at)->format('d/m/Y H:i'),
            'canal'            => $c->canal,
            'objetivo_tipo'    => $c->objetivo_tipo,
            'objetivo_nombre'  => $this->nombreObjetivo($c),
            'audiencia'        => $this->audienciaLabel($c),
            'paises'           => $this->nombresPaises($c->paises),
            'destinatarios'    => $destinatarios,
            'estado'           => $c->estado,
            'conversion'       => $conversionAplica ? $conversion : null,
            'conversion_pct'   => $pct,
            'conversion_label' => $conversionLabel,
            'admin'            => $c->admin ? trim($c->admin->nombres . ' ' . $c->admin->apellidoPaterno) : null,
        ];
    }

    /**
     * Conversión atribuida a la comunicación: [conteo, label, aplica].
     *  - actividad: destinatarios (Personas) inscriptos a la actividad objetivo DESPUÉS
     *    de la comunicación (no cuenta a quienes ya estaban inscriptos antes).
     *  - campaña + suscriptos: leads marcados como convertidos (Suscripciones.convertido).
     *    Aproximado: `convertido` no tiene fecha, así que es "convertidos a hoy".
     *  - campaña + segmento: no hay atribución directa a la campaña todavía.
     */
    private function conversion(Comunicacion $c): array
    {
        if ($c->objetivo_tipo === Comunicacion::OBJETIVO_ACTIVIDAD) {
            $count = ComunicacionDestinatario::where('comunicacion_id', $c->id)
                ->whereNotNull('idPersona')
                ->whereIn('idPersona', function ($q) use ($c) {
                    $q->select('idPersona')->from('Inscripcion')
                        ->where('idActividad', $c->objetivo_id)
                        ->where('fechaInscripcion', '>=', $c->created_at)
                        ->whereNull('deleted_at');
                })->count();

            return [$count, 'se inscribieron', true];
        }

        if ($c->objetivo_tipo === Comunicacion::OBJETIVO_CAMPANA && $c->segmento === EnviarComunicacionCampania::AUDIENCIA_SUSCRIPTOS) {
            $count = ComunicacionDestinatario::where('comunicacion_id', $c->id)
                ->whereNotNull('suscripcion_id')
                ->whereIn('suscripcion_id', function ($q) {
                    $q->select('id')->from('Suscripciones')->where('convertido', 1);
                })->count();

            return [$count, 'se convirtieron', true];
        }

        return [0, null, false];
    }

    private function nombreObjetivo(Comunicacion $c)
    {
        if ($c->objetivo_tipo === Comunicacion::OBJETIVO_CAMPANA) {
            $camp = Campaign::find($c->objetivo_id);
            return $camp ? $camp->nombre : ('Campaña #' . $c->objetivo_id);
        }

        $act = Actividad::todosLosPaises()->find($c->objetivo_id);
        return $act ? $act->nombreActividad : ('Actividad #' . $c->objetivo_id);
    }

    private function audienciaLabel(Comunicacion $c): string
    {
        if ($c->objetivo_tipo === Comunicacion::OBJETIVO_CAMPANA && $c->segmento === EnviarComunicacionCampania::AUDIENCIA_SUSCRIPTOS) {
            return 'Suscriptos';
        }

        $labels = [
            'coordinadores'          => 'Coordinadores',
            'coordinadores_gestion'  => 'Coord. de gestión',
            'activos'                => 'Activos',
            'frecuentes'             => 'Frecuentes',
            'jefes_cuadrilla'        => 'Jefes de cuadrilla',
            'jefaturas'              => 'Jefaturas',
            'todos'                  => 'Todos',
        ];

        return $labels[$c->segmento] ?? (string) $c->segmento;
    }

    private function nombresPaises($ids): string
    {
        if (empty($ids)) {
            return '';
        }

        return Pais::whereIn('id', (array) $ids)->pluck('nombre')->implode(', ');
    }

    /**
     * Cuenta cuántas personas recibirían la invitación con el criterio elegido,
     * para mostrarlo antes de enviar (transparencia). Mismo criterio exacto que
     * el envío real (EnviarInvitacionActividad::segmento).
     */
    public function preview(Request $request)
    {
        $data = $this->validar($request, false);

        // total = tamaño de la audiencia (ignora opt-in); por_canal = alcanzables por cada
        // canal elegido. destinatarios = suma de envíos (una persona alcanzable por 2 canales
        // recibe 2). Así el cálculo queda discriminado por canal.
        list($total, $porCanal) = $this->contar($data);
        $resumen = $this->porCanalResumen($total, $porCanal);

        $emailDest = (int) ($porCanal[EnviarInvitacionActividad::CANAL_EMAIL] ?? 0);

        return response()->json([
            'total'                => $total,
            'por_canal'            => $resumen,
            'destinatarios'        => array_sum(array_values($porCanal)),
            // Preflight: a qué ritmo diario sale el email y en cuántos días se completa,
            // para que el admin sepa antes de disparar (Gmail tiene tope diario bajo).
            'email_por_dia'        => (int) config('mailing.hub_por_dia'),
            'email_dias_estimados' => MailThrottle::diasEstimados($emailDest),
        ]);
    }

    /**
     * Cuenta [total, alcanzablesPorCanal] según el objetivo y la audiencia elegidos.
     * `total` es el tamaño de la audiencia ignorando el opt-in (único, no depende del canal);
     * `alcanzablesPorCanal` es un mapa canal => cuántos pueden recibir por ese canal.
     *  - actividad: por cada canal elegido (push/email), distinto opt-in.
     *  - campaña + segmento: voluntarios del segmento por email.
     *  - campaña + suscriptos: leads de la campaña con mail vs todos.
     */
    private function contar(array $data): array
    {
        $anios = $data['anios'] ?? [];

        if ($data['objetivo'] === 'campania') {
            $canal = EnviarInvitacionActividad::CANAL_EMAIL;

            if ($data['audiencia'] === EnviarComunicacionCampania::AUDIENCIA_SEGMENTO) {
                $total = EnviarInvitacionActividad::segmento($data['idsPaises'], $data['segmento'], $canal, false, $anios)->count();
                $alc   = EnviarInvitacionActividad::segmento($data['idsPaises'], $data['segmento'], $canal, true, $anios)->count();
                return [$total, [$canal => $alc]];
            }

            // Suscriptos de la campaña: alcanzables = con mail; total = todos los suscriptos.
            $idCampania = (int) $data['idCampania'];
            $total = Suscribe::where('campaign_id', $idCampania)->count();
            $alc   = EnviarComunicacionCampania::suscriptosAlcanzables($idCampania)->count();
            return [$total, [$canal => $alc]];
        }

        // Actividad: el total (ignora opt-in) es único; alcanzables por cada canal elegido.
        $total = EnviarInvitacionActividad::segmento(
            $data['idsPaises'], $data['segmento'], EnviarInvitacionActividad::CANAL_EMAIL, false, $anios
        )->count();

        $porCanal = [];
        foreach ($data['canales'] as $canal) {
            $porCanal[$canal] = EnviarInvitacionActividad::segmento($data['idsPaises'], $data['segmento'], $canal, true, $anios)->count();
        }

        return [$total, $porCanal];
    }

    /** Convierte el mapa canal=>alcanzables a la lista para el front, con sin_canal. */
    private function porCanalResumen(int $total, array $porCanal): array
    {
        $out = [];
        foreach ($porCanal as $canal => $alc) {
            $out[] = ['canal' => $canal, 'alcanzables' => $alc, 'sin_canal' => max(0, $total - $alc)];
        }
        return $out;
    }

    /** Versión de texto plano de un mensaje (posible HTML) para push, recortada. */
    private function aTextoPlano(string $mensaje, int $max): string
    {
        $plano = trim(preg_replace('/\s+/', ' ', strip_tags($mensaje)));
        return mb_substr($plano, 0, $max);
    }

    /**
     * Valida, cuenta y despacha el job de envío. Devuelve el conteo para feedback.
     */
    public function enviar(Request $request)
    {
        $data = $this->validar($request, true);

        list($total, $porCanal) = $this->contar($data);
        $resumen = $this->porCanalResumen($total, $porCanal);
        $destinatarios = array_sum(array_values($porCanal));

        // Freno de mano: tope duro de destinatarios por email por envío (si está activado
        // por config). Corta ANTES de encolar nada para evitar un "todos" gigante accidental.
        $emailDest = (int) ($porCanal[EnviarInvitacionActividad::CANAL_EMAIL] ?? 0);
        $maxPorEnvio = (int) config('mailing.hub_max_por_envio');
        if ($maxPorEnvio > 0 && $emailDest > $maxPorEnvio) {
            abort(response()->json([
                'message' => 'Envío demasiado grande',
                'errors'  => ['destinatarios' => [
                    "El envío por email alcanza a {$emailDest} personas y supera el tope por envío ({$maxPorEnvio}). Segmentá o dividí el envío.",
                ]],
            ], 422));
        }

        $diasEstimados = MailThrottle::diasEstimados($emailDest);

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
                auth()->user()->idPersona,
                $data['anios']
            );

            return response()->json(['ok' => true, 'destinatarios' => $destinatarios, 'por_canal' => $resumen, 'email_dias_estimados' => $diasEstimados]);
        }

        // Objetivo actividad: la actividad debe existir y ser visible para el admin (el
        // global scope de país aplica acá al haber usuario autenticado). Si no, 404.
        $actividad = Actividad::find($data['idActividad']);
        if (!$actividad) {
            abort(404);
        }

        // Un envío por canal elegido. El push toma una versión de texto plano recortada del
        // mensaje (que para email puede ser HTML) y un título acotado a 65.
        foreach ($data['canales'] as $canal) {
            $esPush       = $canal === EnviarInvitacionActividad::CANAL_PUSH;
            $tituloCanal  = $esPush ? mb_substr($data['titulo'], 0, 65) : $data['titulo'];
            $mensajeCanal = $esPush ? $this->aTextoPlano($data['mensaje'], 240) : $data['mensaje'];

            EnviarInvitacionActividad::dispatch(
                (int) $data['idActividad'],
                $data['idsPaises'],
                $data['segmento'],
                $tituloCanal,
                $mensajeCanal,
                auth()->user()->idPersona,
                $canal,
                $data['anios']
            );
        }

        return response()->json(['ok' => true, 'destinatarios' => $destinatarios, 'por_canal' => $resumen]);
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
            'anios'       => 'sometimes|array',
            'anios.*'     => 'integer',
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
            // Actividad: uno o más canales (push/email).
            $reglas['canales']   = 'required|array|min:1';
            $reglas['canales.*'] = 'in:' . implode(',', EnviarInvitacionActividad::CANALES);
            $reglas['segmento']  = 'required|in:' . implode(',', EnviarInvitacionActividad::SEGMENTOS);
            if ($requiereContenido) {
                // Límites según los canales: si va push, el título es corto (65) por la
                // plataforma; si va email, el cuerpo admite HTML largo (20000). Con ambos,
                // el push usa una versión de texto plano recortada del mismo mensaje.
                $canales      = (array) $request->input('canales', []);
                $incluyePush  = in_array(EnviarInvitacionActividad::CANAL_PUSH, $canales, true);
                $incluyeEmail = in_array(EnviarInvitacionActividad::CANAL_EMAIL, $canales, true);
                $reglas['idActividad'] = 'required|integer';
                $reglas['titulo']      = 'required|string|max:' . ($incluyePush ? 65 : 150);
                $reglas['mensaje']     = 'required|string|max:' . ($incluyeEmail ? 20000 : 240);
            }
        }

        $data = $request->validate($reglas);
        $data['objetivo'] = $objetivo;
        $data['anios'] = array_values(array_filter(array_map('intval', (array) ($data['anios'] ?? []))));

        // Canales efectivos: campaña se fija en email server-side (no se confía en el cliente).
        if ($objetivo === 'campania') {
            $data['canales'] = [EnviarInvitacionActividad::CANAL_EMAIL];
        } else {
            $data['canales'] = array_values(array_unique((array) ($data['canales'] ?? [])));
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
