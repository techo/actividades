<?php

namespace App\Jobs;

use App\Actividad;
use App\CoordinadorComunidad;
use App\CoordinadorEquipo;
use App\Persona;
use App\Services\Push\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Envía una invitación push (texto libre compuesto por un admin) a un segmento de
 * personas, invitándolas a participar de una actividad puntual.
 *
 * Diseño y garantías de privacidad:
 *  - No exporta ni mueve datos personales: la comunicación es 100% in-app (push).
 *  - El opt-in (`recibir_push`) y la existencia de dispositivo activo se respetan en
 *    PushNotificationService::enviar(); este job no los reimplementa.
 *  - El scope de país ya viene validado por el controlador contra el país permitido
 *    del admin. El job recibe `idsPaises` ya autorizados y solo los ejecuta.
 *  - No incluye ningún "motivo"/perfilado de la persona: solo el mensaje del admin
 *    y el id de la actividad para que la app arme la navegación.
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

    /** Identificadores de segmento válidos (fuente única, la usa también el validator). */
    const SEGMENTOS = [
        'coordinadores',            // rol Spatie global 'coordinador'
        'coordinadores_gestion',    // coordina alguna actividad/equipo/comunidad (membresía real)
        'activos',                  // se inscribió a algo en los últimos DIAS_ACTIVIDAD_RECIENTE días
        'frecuentes',               // MIN_PARTICIPACIONES+ asistencias (presente=1) históricas
        'todos',                    // todos los voluntarios con push activas
    ];

    /** @var int */
    protected $idActividad;

    /** @var int[] Ids de país ya validados contra el scope del admin. */
    protected $idsPaises;

    /** @var string 'coordinadores' | 'todos' */
    protected $segmento;

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
        $idAdmin = null
    ) {
        $this->idActividad = $idActividad;
        $this->idsPaises   = array_values(array_unique(array_map('intval', $idsPaises)));
        $this->segmento    = $segmento;
        $this->titulo      = $titulo;
        $this->mensaje     = $mensaje;
        $this->idAdmin     = $idAdmin;
    }

    /**
     * Fuente única de verdad de la segmentación. La usan tanto el preview del
     * controlador (para contar destinatarios antes de enviar) como el envío real,
     * así el número que ve el admin es exactamente el criterio que se ejecuta.
     *
     * @param  int[]  $idsPaises
     * @param  string $segmento  'coordinadores' | 'todos'
     */
    public static function segmento(array $idsPaises, string $segmento): Builder
    {
        // Base común a todos los segmentos: país + opt-in de push + dispositivo activo.
        $query = Persona::query()
            ->whereIn('idPais', $idsPaises)
            ->where('recibir_push', true)
            ->whereHas('dispositivos', function ($q) {
                $q->where('activo', true);
            });

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

            // Se inscribió a alguna actividad en la ventana reciente (engagement).
            case 'activos':
                $query->whereHas('inscripciones', function ($q) {
                    $q->where('fechaInscripcion', '>=', now()->subDays(self::DIAS_ACTIVIDAD_RECIENTE));
                });
                break;

            // Asistió de verdad (presente=1) al menos MIN_PARTICIPACIONES veces.
            // SoftDeletes de Inscripcion excluye borradas automáticamente.
            case 'frecuentes':
                $query->whereHas('inscripciones', function ($q) {
                    $q->where('presente', 1);
                }, '>=', self::MIN_PARTICIPACIONES);
                break;

            // 'todos': sin filtro adicional sobre la base.
            case 'todos':
            default:
                break;
        }

        return $query;
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

        $enviados = 0;

        self::segmento($this->idsPaises, $this->segmento)
            ->with('pais')
            ->chunk(100, function ($personas) use ($pushService, $datos, &$enviados) {
                foreach ($personas as $persona) {
                    // enviar() verifica recibir_push + dispositivo activo internamente.
                    $pushService->enviar($persona, $this->titulo, $this->mensaje, $datos);
                    $enviados++;
                }
            });

        // Registro de auditoría: quién envió, a qué segmento y a cuántos. Nunca se
        // loguea el listado de destinatarios (sería recrear el dump que evitamos).
        Log::info('EnviarInvitacionActividad: invitación despachada', [
            'idAdmin'       => $this->idAdmin,
            'idActividad'   => $this->idActividad,
            'idsPaises'     => $this->idsPaises,
            'segmento'      => $this->segmento,
            'destinatarios' => $enviados,
        ]);
    }
}
