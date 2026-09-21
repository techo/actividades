<?php

namespace App\Services\CalidadDatos;

use App\Persona;
use App\Services\Documento\DocumentoService;
use Carbon\Carbon;

/**
 * Puntúa la CONFIANZA que merecen los datos identitarios de una persona
 * (calidad de datos progresiva). No dice si un dato es "correcto" —eso no se
 * puede saber— sino cuán SOSPECHOSO es, con señales baratas y objetivas.
 *
 * Evalúa cuatro campos (los que necesita una ficha de seguro):
 *   - documento: reutiliza DocumentoService (formato + dígito verificador por
 *     país; estricto si la persona eligió tipo_documento).
 *   - fecha_nacimiento: edad plausible (no futura, ni <5, ni >100).
 *   - nombre / apellido: heurísticas de "basura" (dígitos, blocklist, repetido,
 *     demasiado corto). NO penaliza minúsculas ni un solo token: detectar un
 *     apodo real ("Pancho" por "Francisco") es inherentemente débil y preferimos
 *     no molestar a datos buenos con falsos positivos.
 *
 * Contempla la ÚLTIMA verificación explícita del usuario (datos_verificados_at):
 * `verificado` indica si confirmó dentro de la vigencia, insumo para no volver a
 * pedirle que revise (ver necesitaVerificacion()).
 *
 * El resultado (array) está pensado para inyectarse en una fila de listado y
 * serializarse a JSON; los `motivo` son keys i18n (backend.dq_*) que traduce la
 * celda Vue con el locale del usuario que mira el backoffice.
 */
class CalidadDatosPersona
{
    /** Meses que una verificación explícita se considera vigente (fallback si no
     * hay config; el valor efectivo lo lee de config('calidad_datos.vigencia_meses')). */
    const VIGENCIA_MESES = 12;

    /** Ventana de edad plausible para una persona real del sistema. */
    const EDAD_MINIMA_PLAUSIBLE = 5;
    const EDAD_MAXIMA_PLAUSIBLE = 100;

    /**
     * Pesos por campo (suman 100). Un campo 'ok' suma su peso; 'baja' (presente
     * pero sospechoso) y 'sin_dato' suman 0 —para la confiabilidad, un dato mal
     * cargado es tan inútil como uno ausente—.
     */
    const PESOS = [
        'documento'        => 35,
        'fecha_nacimiento' => 25,
        'nombre'           => 20,
        'apellido'         => 20,
    ];

    /** Valores "basura" típicos que aparecen en nombre/apellido. */
    private static $nombresBasura = [
        'test', 'testing', 'prueba', 'xxx', 'xx', 'asd', 'asdf', 'aaa', 'nn',
        'na', 'n/a', 'sinnombre', 'ninguno', 'nombre', 'apellido', 'no', 'si',
    ];

    /** @var DocumentoService */
    private $doc;

    public function __construct(DocumentoService $doc = null)
    {
        $this->doc = $doc ?? new DocumentoService();
    }

    /**
     * Evalúa una persona. Resuelve el país vía DocumentoService (usa DB para la
     * abreviación, cacheada por idPais).
     *
     * @return array{puntaje:int, nivel:string, campos:array, motivos:array, verificado:bool, verificado_at:?string}
     */
    public function evaluar(Persona $persona): array
    {
        $docValido = $this->doc
            ->validarComoTipo($persona->tipo_documento, $persona->dni, $persona->idPais)['valido'];

        return $this->componer($persona, $docValido);
    }

    /**
     * Igual que evaluar() pero recibe la abreviación del país directamente, sin
     * tocar la base (para tests unitarios sobre personas no persistidas).
     */
    public function evaluarConAbreviacion(Persona $persona, ?string $abreviacion): array
    {
        if (!empty($persona->tipo_documento)) {
            // Estricto contra el tipo, sin chequeo país (idPais null lo saltea).
            $docValido = $this->doc->validarComoTipo($persona->tipo_documento, $persona->dni, null)['valido'];
        } else {
            $docValido = $this->doc->validarPorAbreviacion($abreviacion, $persona->dni)['valido'];
        }

        return $this->componer($persona, $docValido);
    }

    /**
     * ¿Conviene pedirle a la persona que revise sus datos? Sí cuando hay algo
     * para revisar y NO confirmó dentro de la vigencia. Insumo del futuro paso de
     * microvalidación en la inscripción: no molestar a quien ya validó hace poco.
     */
    public function necesitaVerificacion(Persona $persona): bool
    {
        if ($this->verificadoVigente($persona)) {
            return false;
        }
        return $this->evaluar($persona)['nivel'] !== 'ok';
    }

    // ── Interno ───────────────────────────────────────────────────────────────

    private function componer(Persona $persona, bool $docValido): array
    {
        $campos = [
            'nombre'           => $this->evalNombre($persona->nombres, 'dq_name'),
            'apellido'         => $this->evalNombre($persona->apellidoPaterno, 'dq_lastname'),
            'documento'        => $this->evalDocumento($persona->dni, $docValido),
            'fecha_nacimiento' => $this->evalFecha($persona->fechaNacimiento),
        ];

        $puntaje = 0;
        $motivos = [];
        foreach (self::PESOS as $key => $peso) {
            if ($campos[$key]['estado'] === 'ok') {
                $puntaje += $peso;
            } elseif ($campos[$key]['motivo'] !== null) {
                $motivos[] = $campos[$key]['motivo'];
            }
        }

        return [
            'puntaje'       => $puntaje,
            'nivel'         => $this->nivel($puntaje, $campos),
            'campos'        => $campos,
            'motivos'       => $motivos,
            'verificado'    => $this->verificadoVigente($persona),
            'verificado_at' => optional($persona->datos_verificados_at)->toDateString(),
        ];
    }

    private function nivel(int $puntaje, array $campos): string
    {
        // Un valor positivamente MAL cargado (presente pero inválido) es lo más
        // grave: siempre 'critico', aunque el resto sume puntos.
        foreach ($campos as $c) {
            if ($c['estado'] === 'baja') {
                return 'critico';
            }
        }
        if ($puntaje < 55) {
            return 'critico';
        }
        if ($puntaje < 100) {
            return 'revisar';
        }
        return 'ok';
    }

    private function evalNombre($valor, string $motivoBase): array
    {
        $v = trim((string) $valor);
        if ($v === '') {
            return ['estado' => 'sin_dato', 'motivo' => 'backend.' . $motivoBase . '_missing'];
        }
        if ($this->pareceBasura($v)) {
            return ['estado' => 'baja', 'motivo' => 'backend.' . $motivoBase . '_suspicious'];
        }
        return ['estado' => 'ok', 'motivo' => null];
    }

    private function pareceBasura(string $v): bool
    {
        $limpio = mb_strtolower(preg_replace('/\s+/', '', $v));

        if (mb_strlen($v) < 2) {
            return true; // demasiado corto
        }
        if (preg_match('/\d/', $v)) {
            return true; // contiene dígitos
        }
        if (in_array($limpio, self::$nombresBasura, true)) {
            return true; // valor de relleno conocido
        }
        if (preg_match('/^(.)\1+$/u', $limpio)) {
            return true; // un solo carácter repetido (aaa, xxxx)
        }
        return false;
    }

    private function evalDocumento($valor, bool $valido): array
    {
        $v = trim((string) $valor);
        if ($v === '') {
            return ['estado' => 'sin_dato', 'motivo' => 'backend.dq_doc_missing'];
        }
        if (!$valido) {
            return ['estado' => 'baja', 'motivo' => 'backend.dq_doc_invalid'];
        }
        return ['estado' => 'ok', 'motivo' => null];
    }

    private function evalFecha($valor): array
    {
        if (empty($valor)) {
            return ['estado' => 'sin_dato', 'motivo' => 'backend.dq_birth_missing'];
        }
        try {
            $fecha = $valor instanceof Carbon ? $valor : Carbon::parse($valor);
        } catch (\Exception $e) {
            return ['estado' => 'baja', 'motivo' => 'backend.dq_birth_invalid'];
        }

        $edad = $fecha->age;
        if ($fecha->isFuture() || $edad < self::EDAD_MINIMA_PLAUSIBLE || $edad > self::EDAD_MAXIMA_PLAUSIBLE) {
            return ['estado' => 'baja', 'motivo' => 'backend.dq_birth_implausible'];
        }
        return ['estado' => 'ok', 'motivo' => null];
    }

    private function verificadoVigente(Persona $persona): bool
    {
        if (empty($persona->datos_verificados_at)) {
            return false;
        }
        $at = $persona->datos_verificados_at instanceof Carbon
            ? $persona->datos_verificados_at
            : Carbon::parse($persona->datos_verificados_at);

        $meses = (int) config('calidad_datos.vigencia_meses', self::VIGENCIA_MESES);

        return $at->gte(Carbon::now()->subMonths($meses));
    }
}
