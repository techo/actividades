<?php

use App\Actividad;
use App\Inscripcion;
use App\Persona;
use App\PuntoEncuentro;
use App\Services\Salesforce\SocioExencionService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Data de prueba para SANDBOX. NO correr en producción.
 *
 * Crea actividades de distinta índole (paga / gratuita / con confirmación /
 * Argentina / Brasil) e inscribe personas YA EXISTENTES usando el flujo real
 * (SocioExencionService, que consulta Salesforce). Sirve para probar de punta a
 * punta la exención de pago para socios y ver listados con inscriptos.
 *
 * Es idempotente: borra sus propias actividades marcadas "[DEMO]" (y sus
 * inscripciones) antes de recrearlas, así se puede re-ejecutar para resetear.
 *
 *   php artisan db:seed --class=SandboxDemoSeeder --force
 */
class SandboxDemoSeeder extends Seeder
{
    const PREFIJO = '[DEMO]';
    const PAIS_AR = 13;
    const PAIS_BR = 33;

    /** DNI real de un socio/donante en Salesforce (para que dispare la exención). */
    const DNI_SOCIO    = '24134990';
    /** DNI que NO es socio (determinístico, para el caso "paga normal"). */
    const DNI_NO_SOCIO = '99000001';

    const PASSWORD_DEMO = 'demo1234';

    public function run()
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('SandboxDemoSeeder NO debe correr en producción.');
        }

        $this->limpiarDemoPrevio();

        // ── Personas existentes ────────────────────────────────────────────────
        $socio    = $this->prepararPersonaAr(0, self::DNI_SOCIO,    'socio.demo');
        $noSocio  = $this->prepararPersonaAr(1, self::DNI_NO_SOCIO, 'nosocio.demo');

        // El socio demo es además admin de Argentina para entrar al backoffice.
        // idPaisPermitido = 13 (NO 0): aunque el scope de Actividad trata 0 como
        // "global", muchas secciones (Usuarios, Coordinadores, Oficinas, Provincias,
        // Campañas…) filtran directo por idPaisPermitido y con 0 quedan vacías, y
        // CampanaPolicy::create exige idPaisPermitido > 0.
        $socio->idPaisPermitido = self::PAIS_AR;
        $socio->save();
        $socio->assignRole('admin');
        $extrasAr = Persona::where('idPais', self::PAIS_AR)
            ->whereNotNull('mail')->where('mail', 'like', '%@%')
            ->orderBy('idPersona')->skip(2)->take(4)->get();

        // ── Provincias válidas por país (fallback defensivo) ───────────────────
        $provAr = DB::table('atl_provincias')->where('id_pais', self::PAIS_AR)->value('id') ?? 1;
        $provBr = DB::table('atl_provincias')->where('id_pais', self::PAIS_BR)->value('id') ?? $provAr;

        // ── Actividades de distinta índole ─────────────────────────────────────
        $constPagaAr = $this->crearActividad([
            'nombre' => 'Construcción paga (AR)', 'idTipo' => 1, 'idPais' => self::PAIS_AR,
            'idProvincia' => $provAr, 'pago' => 1, 'confirmacion' => 0,
            'montoMin' => 5000, 'moneda' => 'ARS', 'metodos_pago' => ['transferencia' => true, 'tarjeta' => true, 'link_pix' => false],
            'permite_exencion' => 1,
        ]);

        $masivaGratisAr = $this->crearActividad([
            'nombre' => 'Actividad masiva gratuita (AR)', 'idTipo' => 14, 'idPais' => self::PAIS_AR,
            'idProvincia' => $provAr, 'pago' => 0, 'confirmacion' => 0,
        ]);

        $colectaPagaConfAr = $this->crearActividad([
            'nombre' => 'Colecta paga con confirmación (AR)', 'idTipo' => 16, 'idPais' => self::PAIS_AR,
            'idProvincia' => $provAr, 'pago' => 1, 'confirmacion' => 1,
            'montoMin' => 3000, 'moneda' => 'ARS', 'metodos_pago' => ['transferencia' => true, 'tarjeta' => false, 'link_pix' => false],
        ]);

        $voluntariadoConfAr = $this->crearActividad([
            'nombre' => 'Voluntariado con confirmación sin pago (AR)', 'idTipo' => 2, 'idPais' => self::PAIS_AR,
            'idProvincia' => $provAr, 'pago' => 0, 'confirmacion' => 1,
        ]);

        $constPagaBr = $this->crearActividad([
            'nombre' => 'Construcción paga (Brasil)', 'idTipo' => 1, 'idPais' => self::PAIS_BR,
            'idProvincia' => $provBr, 'pago' => 1, 'confirmacion' => 0,
            'montoMin' => 50, 'moneda' => 'BRL', 'metodos_pago' => ['transferencia' => true, 'tarjeta' => true, 'link_pix' => true],
        ]);

        // ── Inscripciones (flujo real: aplica exención vía Salesforce) ─────────
        // OJO: NO inscribimos al socio en $constPagaAr a propósito: queda libre para
        // que uno pueda entrar como socio y probar EN VIVO el flujo → cartel en gracias.
        // (Si el socio ya está inscripto, no se puede rehacer el flujo y no se ve el cartel.)
        $this->inscribir($colectaPagaConfAr, $socio);  // → exento pero WAITING_CONFIRMATION (ejemplo backoffice)
        $this->inscribir($constPagaBr, $socio);        // → NO exento (actividad de Brasil)

        // No socio AR → paga normal / gratis confirmado.
        $this->inscribir($constPagaAr, $noSocio);                       // → CONFIRM_BY_PAYING
        $this->inscribir($masivaGratisAr, $noSocio, ['confirma' => 1]); // → CONFIRMADO

        // Volumen con personas existentes (sin tocarles nada).
        foreach ($extrasAr as $i => $p) {
            $this->inscribir($masivaGratisAr, $p, ['confirma' => 1, 'presente' => $i % 2]);
            $this->inscribir($voluntariadoConfAr, $p, ['confirma' => $i % 2]);
        }

        $this->command->info('── SandboxDemoSeeder OK ──');
        $this->command->info('Socio    → mail: ' . $socio->mail . '  pass: ' . self::PASSWORD_DEMO . '  (DNI ' . self::DNI_SOCIO . ')');
        $this->command->info('No socio → mail: ' . $noSocio->mail . '  pass: ' . self::PASSWORD_DEMO . '  (DNI ' . self::DNI_NO_SOCIO . ')');
        $this->command->getOutput()->writeln($this->tablaResumen());
    }

    /** Borra actividades demo previas y sus inscripciones (reset idempotente). */
    private function limpiarDemoPrevio()
    {
        $demos = Actividad::withTrashed()->where('nombreActividad', 'like', self::PREFIJO . '%')->get();
        foreach ($demos as $act) {
            Inscripcion::withTrashed()->where('idActividad', $act->idActividad)->forceDelete();
            PuntoEncuentro::where('idActividad', $act->idActividad)->delete();
            $act->forceDelete();
        }
    }

    /**
     * Toma una persona AR existente por offset, le fija DNI, mail demo y password
     * conocido para poder loguearse y probar el flujo. Usa personas YA creadas.
     */
    private function prepararPersonaAr($offset, $dni, $mailLocal)
    {
        $persona = Persona::where('idPais', self::PAIS_AR)
            ->whereNotNull('mail')->where('mail', 'like', '%@%')
            ->orderBy('idPersona')->skip($offset)->first();

        $persona->dni               = $dni;
        $persona->mail              = $mailLocal . '+' . $persona->idPersona . '@techo.org';
        $persona->password          = Hash::make(self::PASSWORD_DEMO);
        // Email validado: sin esto MustVerifyEmail bloquea el login.
        $persona->email_verified_at = Carbon::now();
        $persona->save();

        return $persona;
    }

    /** Devuelve el idTipo pedido si está activo; si no, cae a cualquier tipo activo. */
    private function tipoActivo($idTipo)
    {
        $ok = DB::table('Tipo')->where('idTipo', $idTipo)->whereNull('deleted_at')->exists();
        if ($ok) {
            return $idTipo;
        }
        return DB::table('Tipo')->whereNull('deleted_at')->min('idTipo') ?? $idTipo;
    }

    private function crearActividad(array $o)
    {
        $act = Actividad::create([
            'idTipo'                   => $this->tipoActivo($o['idTipo']),
            'nombreActividad'          => self::PREFIJO . ' ' . $o['nombre'],
            'descripcion'              => 'Actividad de prueba generada por SandboxDemoSeeder.',
            'lugar'                    => 'Sede de prueba',
            'idPais'                   => $o['idPais'],
            'idProvincia'              => $o['idProvincia'],
            'fechaInicio'              => Carbon::now()->addDays(14),
            'fechaFin'                 => Carbon::now()->addDays(14)->addHours(4),
            'fechaInicioInscripciones' => Carbon::now()->subDay(),
            'fechaFinInscripciones'    => Carbon::now()->addDays(12),
            'fechaLimitePago'          => Carbon::now()->addDays(10),
            'estadoConstruccion'       => 'Abierta',
            'limiteInscripciones'      => 50,
            'pago'                     => $o['pago'],
            'confirmacion'             => $o['confirmacion'],
            'montoMin'                 => $o['montoMin'] ?? 0,
            'montoMax'                 => 0,
            'moneda'                   => $o['moneda'] ?? null,
            'metodos_pago'             => $o['metodos_pago'] ?? null,
            'permite_exencion'         => $o['permite_exencion'] ?? 0,
            'requiere_ficha_medica'    => 0,
            'requiere_estudios'        => 0,
            'ficha_medica_campos'      => [],
        ]);

        // Sin un PuntoEncuentro con estado=1 la actividad no aparece en el listado
        // público (ActividadesSearch lo exige) ni se puede completar la inscripción.
        PuntoEncuentro::create([
            'punto'       => 'Punto de encuentro demo',
            'horario'     => '09:00:00',
            'idActividad' => $act->idActividad,
            'idPais'      => $o['idPais'],
            'idProvincia' => $o['idProvincia'],
            'estado'      => 1,
        ]);

        return $act;
    }

    private function inscribir(Actividad $actividad, Persona $persona, array $overrides = [])
    {
        $ins = new Inscripcion();
        $ins->idActividad     = $actividad->idActividad;
        $ins->idPersona       = $persona->idPersona;
        $ins->fechaInscripcion = Carbon::now();
        foreach ($overrides as $k => $v) {
            $ins->$k = $v;
        }
        // Flujo real: materializa la exención si corresponde (consulta Salesforce).
        app(SocioExencionService::class)->aplicarSiCorresponde($ins, $actividad, $persona);
        $ins->save();

        return $ins;
    }

    private function tablaResumen()
    {
        $rows = Actividad::where('nombreActividad', 'like', self::PREFIJO . '%')
            ->orderBy('idActividad')->get();
        $out = ["", "idActividad | pais | pago | conf | inscriptos | actividad"];
        foreach ($rows as $a) {
            $out[] = sprintf(
                '%11d | %4d | %4d | %4d | %10d | %s',
                $a->idActividad, $a->idPais, $a->pago, $a->confirmacion,
                Inscripcion::where('idActividad', $a->idActividad)->count(),
                $a->nombreActividad
            );
        }
        return implode("\n", $out);
    }
}
