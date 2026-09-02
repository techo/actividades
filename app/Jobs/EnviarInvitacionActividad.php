<?php

namespace App\Jobs;

use App\Actividad;
use App\Comunicacion;
use App\ComunicacionDestinatario;
use App\CoordinadorComunidad;
use App\CoordinadorEquipo;
use App\Mail\InvitacionActividadMail;
use App\Persona;
use App\Scopes\BelongsToCountryScope;
use App\Services\Push\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envía una invitación (texto libre compuesto por un admin) a un segmento de personas,
 * invitándolas a participar de una actividad puntual. Soporta canal push (in-app) y
 * email; el canal se elige al disparar el envío.
 *
 * Diseño y garantías de privacidad:
 *  - No exporta ni entrega listados: cada persona recibe solo el mensaje del admin, por
 *    su propio canal (push in-app o email a su casilla). No se comparten datos de contacto.
 *  - El opt-in se respeta en la segmentación: push exige `recibir_push` + dispositivo
 *    activo; email exige `recibirMails` + `mail`. El mismo criterio se usa en el preview.
 *  - El scope de país ya viene validado por el controlador contra el país permitido
 *    del admin. El job recibe `idsPaises` ya autorizados y solo los ejecuta.
 *  - No incluye ningún "motivo"/perfilado de la persona: solo el mensaje del admin
 *    y el id de la actividad para armar la navegación / el enlace.
 */
class EnviarInvitacionActividad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    /** Ventana (días) para considerar a alguien "voluntario activo". Ajustable. */
    const DIAS_ACTIVIDAD_RECIENTE = 90;

    /** Umbral de participaciones (presente=1) para "voluntario frecuente". Ajustable. */
    const MIN_PARTICIPACIONES = 3;

    /** Canales de envío soportados. whatsapp queda para una fase futura (WhatsApp Business API). */
    const CANAL_PUSH  = 'push';
    const CANAL_EMAIL = 'email';
    const CANALES     = [self::CANAL_PUSH, self::CANAL_EMAIL];

    /** Rol de inscripción (catálogo roles_actividad_options) que marca a un jefe de cuadrilla. */
    const ROL_JEFE_CUADRILLA = 'liderazgo_cuadrilla';

    /** Roles de inscripción considerados "jefatura / liderazgo" (superset del anterior). */
    const ROLES_JEFATURA = ['liderazgo_cuadrilla', 'liderazgo_escuela', 'liderazgo_trabajo'];

    /**
     * Segmentos derivados de la participación, cuyo país relevante es el de la ACTIVIDAD
     * (donde la persona se inscribió / asistió / ejerció el rol), no el de residencia.
     * Quien participó en un país cuenta para ese país aunque resida en otro.
     */
    const SEGMENTOS_PAIS_ACTIVIDAD = ['activos', 'frecuentes', 'jefes_cuadrilla', 'jefaturas'];

    /** Identificadores de segmento válidos (fuente única, la usa también el validator). */
    const SEGMENTOS = [
        'coordinadores',            // rol Spatie global 'coordinador'
        'coordinadores_gestion',    // coordina alguna actividad/equipo/comunidad (membresía real)
        'activos',                  // se inscribió a algo en los últimos DIAS_ACTIVIDAD_RECIENTE días
        'frecuentes',               // MIN_PARTICIPACIONES+ asistencias (presente=1) históricas
        'jefes_cuadrilla',          // tuvo rol 'liderazgo_cuadrilla' en alguna inscripción
        'jefaturas',                // tuvo alguna jefatura/liderazgo (ROLES_JEFATURA) en alguna inscripción
        'todos',                    // todos los voluntarios con push activas
    ];

    /** @var int */
    protected $idActividad;

    /** @var int[] Ids de país ya validados contra el scope del admin. */
    protected $idsPaises;

    /** @var string 'coordinadores' | 'todos' */
    protected $segmento;

    /** @var string 'push' | 'email' */
    protected $canal;

    /** @var string */
    protected $titulo;

    /** @var string */
    protected $mensaje;

    /** @var int|null idPersona del admin que dispara el envío (trazabilidad). */
    protected $idAdmin;

    public function __construct(
        int $idActividad,
        array $idsPaises,
        string $segmento,
        string $titulo,
        string $mensaje,
        $idAdmin = null,
        string $canal = self::CANAL_PUSH
    ) {
        $this->idActividad = $idActividad;
        $this->idsPaises   = array_values(array_unique(array_map('intval', $idsPaises)));
        $this->segmento    = $segmento;
        $this->titulo      = $titulo;
        $this->mensaje     = $mensaje;
        $this->idAdmin     = $idAdmin;
        $this->canal       = in_array($canal, self::CANALES, true) ? $canal : self::CANAL_PUSH;
    }

    /**
     * Fuente única de verdad de la segmentación. La usan tanto el preview del
     * controlador (para contar destinatarios antes de enviar) como el envío real,
     * así el número que ve el admin es exactamente el criterio que se ejecuta.
     *
     * @param  int[]  $idsPaises
     * @param  string $segmento  'coordinadores' | 'todos'
     * @param  string $canal     'push' (opt-in recibir_push + dispositivo) | 'email' (recibirMails + mail)
     * @param  bool   $conOptIn  true = solo alcanzables por el canal; false = tamaño total del
     *                           segmento (ignora el opt-in). Lo usa el preview para mostrar
     *                           "llega a N de M" y cuántos no pueden recibir por el canal.
     */
    public static function segmento(array $idsPaises, string $segmento, string $canal = self::CANAL_PUSH, bool $conOptIn = true): Builder
    {
        $query = Persona::query();

        // Opt-in por canal (se puede omitir para contar el segmento completo):
        //  - push: recibir_push + al menos un dispositivo activo.
        //  - email: recibirMails + tiene mail cargado.
        if ($conOptIn) {
            if ($canal === self::CANAL_EMAIL) {
                $query->where('recibirMails', true)
                    ->whereNotNull('mail')
                    ->where('mail', '<>', '');
            } else {
                $query->where('recibir_push', true)
                    ->whereHas('dispositivos', function ($q) {
                        $q->where('activo', true);
                    });
            }
        }

        // País por RESIDENCIA para la mayoría de los segmentos. Los de jefatura son la
        // excepción: su país se toma de la ACTIVIDAD y se aplica dentro del switch.
        if (!in_array($segmento, self::SEGMENTOS_PAIS_ACTIVIDAD, true)) {
            $query->whereIn('idPais', $idsPaises);
        }

        switch ($segmento) {
            // Rol global Spatie. Distinto de "membresía real" (ver coordinadores_gestion).
            case 'coordinadores':
                $query->role('coordinador');
                break;

            // Coordina al menos una actividad (tabla Coordinadores, relación actividades())
            // o algún equipo/comunidad. Persona no tiene relación hacia esas dos pivote,
            // así que se resuelven con subquery por idPersona.
            case 'coordinadores_gestion':
                $query->where(function ($q) {
                    $q->whereHas('actividades')
                        ->orWhereIn('idPersona', CoordinadorEquipo::query()->select('idPersona'))
                        ->orWhereIn('idPersona', CoordinadorComunidad::query()->select('idPersona'));
                });
                break;

            // Se inscribió a una actividad (de los países elegidos) en la ventana reciente.
            case 'activos':
                $query->whereHas('inscripciones', function ($q) use ($idsPaises) {
                    $q->where('fechaInscripcion', '>=', now()->subDays(self::DIAS_ACTIVIDAD_RECIENTE));
                    self::inscripcionEnPaises($q, $idsPaises);
                });
                break;

            // Asistió de verdad (presente=1) al menos MIN_PARTICIPACIONES veces a actividades
            // de los países elegidos. SoftDeletes de Inscripcion excluye borradas.
            case 'frecuentes':
                $query->whereHas('inscripciones', function ($q) use ($idsPaises) {
                    $q->where('presente', 1);
                    self::inscripcionEnPaises($q, $idsPaises);
                }, '>=', self::MIN_PARTICIPACIONES);
                break;

            // Ejerció de jefe de cuadrilla (liderazgo_cuadrilla) en una actividad de alguno
            // de los países elegidos. El país se toma de la ACTIVIDAD, no de la residencia.
            case 'jefes_cuadrilla':
                $query->whereHas('inscripciones', function ($q) use ($idsPaises) {
                    $q->whereIn('rol', [self::ROL_JEFE_CUADRILLA]);
                    self::inscripcionEnPaises($q, $idsPaises);
                });
                break;

            // Ejerció alguna jefatura/liderazgo (cuadrilla, escuela o trabajo) en una actividad
            // de los países elegidos. Superset del anterior; país tomado de la actividad.
            case 'jefaturas':
                $query->whereHas('inscripciones', function ($q) use ($idsPaises) {
                    $q->whereIn('rol', self::ROLES_JEFATURA);
                    self::inscripcionEnPaises($q, $idsPaises);
                });
                break;

            // 'todos': sin filtro adicional sobre la base.
            case 'todos':
            default:
                break;
        }

        return $query;
    }

    /**
     * Restringe una subquery de inscripciones a las que pertenecen a una ACTIVIDAD de los
     * países elegidos (el país relevante para los segmentos de participación es el de la
     * actividad, no la residencia de la persona). Ignora el scope de país ambiente
     * (BelongsToCountryScope) para que el criterio sea idéntico en el preview (con admin
     * autenticado en /admin) y en el job (sin auth): el país lo fija `idsPaises`.
     *
     * @param  int[] $idsPaises  países (de la actividad) a los que se acota
     */
    private static function inscripcionEnPaises(Builder $q, array $idsPaises): void
    {
        $q->withoutGlobalScope(BelongsToCountryScope::class)
            ->whereHas('actividad', function ($a) use ($idsPaises) {
                $a->withoutGlobalScope(BelongsToCountryScope::class)
                    ->whereIn('idPais', $idsPaises);
            });
    }

    public function handle(PushNotificationService $pushService): void
    {
        // todosLosPaises(): en modo sync (dev) el job corre dentro del request con
        // usuario autenticado, y el global scope de país filtraría la actividad si
        // fuese de otro país que el permitido del admin. El país ya se validó arriba.
        $actividad = Actividad::todosLosPaises()->find($this->idActividad);

        if (!$actividad) {
            Log::warning('EnviarInvitacionActividad: actividad inexistente', [
                'idActividad' => $this->idActividad,
                'idAdmin'     => $this->idAdmin,
            ]);
            return;
        }

        // Convención existente del stack de push: la app arma la navegación a partir
        // de `tipo` + `idActividad`. No se construye deep link acá.
        $datos = [
            'tipo'        => 'actividad',
            'estado'      => 'INVITACION',
            'idActividad' => $actividad->idActividad,
        ];

        // Columna vertebral: se registra la comunicación antes del fan-out. El detalle
        // por persona (comunicacion_destinatarios) es el insumo de atribución/conversión.
        $comunicacion = Comunicacion::create([
            'canal'               => $this->canal,
            'objetivo_tipo'       => Comunicacion::OBJETIVO_ACTIVIDAD,
            'objetivo_id'         => $actividad->idActividad,
            'segmento'            => $this->segmento,
            'paises'              => $this->idsPaises,
            'titulo'              => $this->titulo,
            'mensaje'             => $this->mensaje,
            'destinatarios_count' => 0,
            'idAdmin'             => $this->idAdmin,
            'estado'              => Comunicacion::ESTADO_PROCESANDO,
        ]);

        $enviados = 0;
        $ahora    = now();

        self::segmento($this->idsPaises, $this->segmento, $this->canal)
            ->with('pais')
            ->chunk(100, function ($personas) use ($pushService, $actividad, $datos, $comunicacion, $ahora, &$enviados) {
                $filas = [];
                foreach ($personas as $persona) {
                    // El opt-in del canal ya se garantizó en la segmentación; el envío no
                    // lo reimplementa (para push, enviar() igual lo re-verifica internamente).
                    if ($this->canal === self::CANAL_EMAIL) {
                        Mail::to($persona->mail)->queue(
                            new InvitacionActividadMail($persona, $actividad, $this->titulo, $this->mensaje)
                        );
                    } else {
                        $pushService->enviar($persona, $this->titulo, $this->mensaje, $datos);
                    }

                    $filas[] = [
                        'comunicacion_id' => $comunicacion->id,
                        'idPersona'       => $persona->idPersona,
                        'estado'          => ComunicacionDestinatario::ESTADO_ENVIADO,
                        'created_at'      => $ahora,
                        'updated_at'      => $ahora,
                    ];
                    $enviados++;
                }

                // Un insert por chunk (100 filas) en vez de uno por persona.
                if (!empty($filas)) {
                    ComunicacionDestinatario::insert($filas);
                }
            });

        $comunicacion->update([
            'destinatarios_count' => $enviados,
            'estado'              => Comunicacion::ESTADO_ENVIADA,
        ]);

        // Registro de auditoría: quién envió, a qué segmento y a cuántos. Nunca se
        // loguea el listado de destinatarios (sería recrear el dump que evitamos).
        Log::info('EnviarInvitacionActividad: invitación despachada', [
            'idComunicacion' => $comunicacion->id,
            'idAdmin'        => $this->idAdmin,
            'idActividad'    => $this->idActividad,
            'canal'          => $this->canal,
            'idsPaises'      => $this->idsPaises,
            'segmento'       => $this->segmento,
            'destinatarios'  => $enviados,
        ]);
    }
}
