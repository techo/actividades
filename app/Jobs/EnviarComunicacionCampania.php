<?php

namespace App\Jobs;

use App\Campaign;
use App\Comunicacion;
use App\ComunicacionDestinatario;
use App\Mail\InvitacionCampaniaMail;
use App\Pais;
use App\Services\MailThrottle;
use App\Suscribe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envía por EMAIL una comunicación cuyo objetivo es una campaña. Dos audiencias:
 *  - 'suscriptos': los leads captados por la campaña (Suscribe, sin cuenta) → a su mail.
 *  - 'segmento': un segmento de voluntarios (Personas) con el mensaje linkeando a la campaña.
 *
 * Campaña va solo por email: los leads no tienen dispositivo y la app todavía no puede
 * abrir una campaña por deep link (queda para cuando mobile lo soporte). Reusa la misma
 * persistencia (comunicaciones / comunicacion_destinatarios) que la invitación a actividad.
 */
class EnviarComunicacionCampania implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    const AUDIENCIA_SUSCRIPTOS = 'suscriptos';
    const AUDIENCIA_SEGMENTO   = 'segmento';
    const AUDIENCIAS = [self::AUDIENCIA_SUSCRIPTOS, self::AUDIENCIA_SEGMENTO];

    /** @var int */
    protected $idCampania;

    /** @var int[] */
    protected $idsPaises;

    /** @var string 'suscriptos' | 'segmento' */
    protected $audiencia;

    /** @var string|null Segmento de voluntarios (solo si audiencia = segmento). */
    protected $segmento;

    /** @var string */
    protected $titulo;

    /** @var string HTML del cuerpo (editor enriquecido). */
    protected $mensaje;

    /** @var int|null idPersona del admin que dispara el envío. */
    protected $idAdmin;

    public function __construct(
        int $idCampania,
        array $idsPaises,
        string $audiencia,
        $segmento,
        string $titulo,
        string $mensaje,
        $idAdmin = null
    ) {
        $this->idCampania = $idCampania;
        $this->idsPaises  = array_values(array_unique(array_map('intval', $idsPaises)));
        $this->audiencia  = in_array($audiencia, self::AUDIENCIAS, true) ? $audiencia : self::AUDIENCIA_SUSCRIPTOS;
        $this->segmento   = $segmento;
        $this->titulo     = $titulo;
        $this->mensaje    = $mensaje;
        $this->idAdmin    = $idAdmin;
    }

    /**
     * Suscriptos de una campaña alcanzables por email (tienen mail cargado). Los leads no
     * tienen flag de opt-in propio; el único requisito para el email es tener `mail`.
     */
    public static function suscriptosAlcanzables(int $idCampania): Builder
    {
        return Suscribe::where('campaign_id', $idCampania)
            ->whereNotNull('mail')
            ->where('mail', '<>', '');
    }

    public function handle(): void
    {
        $campaign = Campaign::find($this->idCampania);

        if (!$campaign) {
            Log::warning('EnviarComunicacionCampania: campaña inexistente', [
                'idCampania' => $this->idCampania,
                'idAdmin'    => $this->idAdmin,
            ]);
            return;
        }

        // País de la campaña: necesario para armar la URL pública /{abreviacion}/campania/{id}.
        $pais = $campaign->pais_id ? Pais::find($campaign->pais_id) : null;

        $comunicacion = Comunicacion::create([
            'canal'               => Comunicacion::CANAL_EMAIL,
            'objetivo_tipo'       => Comunicacion::OBJETIVO_CAMPANA,
            'objetivo_id'         => $campaign->id,
            'segmento'            => $this->audiencia === self::AUDIENCIA_SEGMENTO ? $this->segmento : self::AUDIENCIA_SUSCRIPTOS,
            'paises'              => $this->idsPaises,
            'titulo'              => $this->titulo,
            'mensaje'             => $this->mensaje,
            'destinatarios_count' => 0,
            'idAdmin'             => $this->idAdmin,
            'estado'              => Comunicacion::ESTADO_PROCESANDO,
        ]);

        $enviados = 0;
        $ahora    = now();

        if ($this->audiencia === self::AUDIENCIA_SEGMENTO) {
            // Voluntarios (Personas) del segmento, por email, con el mensaje hacia la campaña.
            EnviarInvitacionActividad::segmento($this->idsPaises, $this->segmento, EnviarInvitacionActividad::CANAL_EMAIL)
                ->with('pais')
                ->chunk(100, function ($personas) use ($campaign, $pais, $comunicacion, $ahora, &$enviados) {
                    $filas = [];
                    foreach ($personas as $persona) {
                        Mail::to($persona->mail)->later(
                            MailThrottle::siguienteSlot(),
                            new InvitacionCampaniaMail($persona->nombres, $campaign, $pais ?: $persona->pais, $this->titulo, $this->mensaje, $persona)
                        );
                        $filas[] = [
                            'comunicacion_id' => $comunicacion->id,
                            'idPersona'       => $persona->idPersona,
                            'suscripcion_id'  => null,
                            'estado'          => ComunicacionDestinatario::ESTADO_ENVIADO,
                            'created_at'      => $ahora,
                            'updated_at'      => $ahora,
                        ];
                        $enviados++;
                    }
                    if (!empty($filas)) {
                        ComunicacionDestinatario::insert($filas);
                    }
                });
        } else {
            // Suscriptos/leads de la campaña, por su mail inline.
            self::suscriptosAlcanzables($this->idCampania)
                ->chunk(100, function ($suscriptos) use ($campaign, $pais, $comunicacion, $ahora, &$enviados) {
                    $filas = [];
                    foreach ($suscriptos as $s) {
                        Mail::to($s->mail)->later(
                            MailThrottle::siguienteSlot(),
                            new InvitacionCampaniaMail((string) $s->nombre, $campaign, $pais, $this->titulo, $this->mensaje)
                        );
                        $filas[] = [
                            'comunicacion_id' => $comunicacion->id,
                            'idPersona'       => null,
                            'suscripcion_id'  => $s->id,
                            'estado'          => ComunicacionDestinatario::ESTADO_ENVIADO,
                            'created_at'      => $ahora,
                            'updated_at'      => $ahora,
                        ];
                        $enviados++;
                    }
                    if (!empty($filas)) {
                        ComunicacionDestinatario::insert($filas);
                    }
                });
        }

        $comunicacion->update([
            'destinatarios_count' => $enviados,
            'estado'              => Comunicacion::ESTADO_ENVIADA,
        ]);

        Log::info('EnviarComunicacionCampania: comunicación despachada', [
            'idComunicacion' => $comunicacion->id,
            'idAdmin'        => $this->idAdmin,
            'idCampania'     => $this->idCampania,
            'audiencia'      => $this->audiencia,
            'segmento'       => $this->segmento,
            'idsPaises'      => $this->idsPaises,
            'destinatarios'  => $enviados,
        ]);
    }
}
