<?php

namespace App\Services\Documento;

use App\Pais;

/**
 * Validación y normalización del documento de identidad de una persona, según
 * su país. Fuente única de verdad (Fase 1): la usan la regla App\Rules\
 * DocumentoValido (validación) y los flujos de alta/edición (normalización al
 * guardar). Las reglas viven en config/documentos.php (data-driven).
 *
 * Diseño clave: "válido" = el valor matchea AL MENOS UN tipo aceptado del país
 * (documento nacional o pasaporte). Así garantizamos calidad (basura no matchea
 * nada) sin dejar afuera a extranjeros (que cargan pasaporte).
 */
class DocumentoService
{
    /** @var array config('documentos') */
    private $config;

    /** Cache idPais => abreviacion, para no repetir el find en una request. */
    private $abreviacionCache = [];

    public function __construct(array $config = null)
    {
        $this->config = $config ?? (array) config('documentos', []);
    }

    /**
     * Valida un documento para un país.
     *
     * @return array{valido:bool, tipo:?string, normalizado:?string}
     *   - Valor vacío => válido con normalizado '' (la PRESENCIA la maneja
     *     required/nullable en cada flujo, no esta clase).
     *   - Si matchea, 'tipo' es la key del tipo y 'normalizado' el valor canónico.
     */
    public function validar($idPais, $valor): array
    {
        return $this->validarPorAbreviacion($this->abreviacionDe($idPais), $valor);
    }

    /**
     * Igual que validar(), pero recibe directamente la abreviación del país. Sirve
     * para testear sin tocar la base (la resolución idPais => abreviación usa el
     * modelo Pais).
     *
     * @return array{valido:bool, tipo:?string, normalizado:?string}
     */
    public function validarPorAbreviacion(?string $abreviacion, $valor): array
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return ['valido' => true, 'tipo' => null, 'normalizado' => ''];
        }

        foreach ($this->tiposParaAbreviacion($abreviacion) as $tipoKey) {
            $tipo = $this->config['tipos'][$tipoKey] ?? null;
            if (!$tipo) {
                continue;
            }
            $norm = $this->aplicarNormalizador($tipo['normaliza'] ?? 'alnum', $valor);
            if ($norm === '') {
                continue;
            }
            if (!preg_match($tipo['regex'], $norm)) {
                continue;
            }
            if (isset($tipo['check']) && !$this->pasaCheck($tipo['check'], $norm)) {
                continue;
            }
            return ['valido' => true, 'tipo' => $tipoKey, 'normalizado' => $norm];
        }

        return ['valido' => false, 'tipo' => null, 'normalizado' => null];
    }

    /**
     * ¿Es válido el documento para el país? (atajo booleano para la Rule.)
     */
    public function esValido($idPais, $valor): bool
    {
        return $this->validar($idPais, $valor)['valido'];
    }

    /**
     * Devuelve el documento normalizado (canónico) para guardar. Si el valor no
     * matchea ningún tipo (p. ej. un flujo que no corrió la validación), devuelve
     * el valor original trimmeado para no perder dato.
     */
    public function normalizar($idPais, $valor): string
    {
        $r = $this->validar($idPais, $valor);
        if ($r['valido'] && $r['normalizado'] !== null) {
            return $r['normalizado'];
        }
        return trim((string) $valor);
    }

    /**
     * Labels (keys i18n de documento.php) de los tipos aceptados por el país.
     * Sirve para armar un mensaje de error entendible ("DNI o pasaporte").
     *
     * @return string[]
     */
    public function labelsPara($idPais): array
    {
        $labels = [];
        foreach ($this->tiposParaAbreviacion($this->abreviacionDe($idPais)) as $tipoKey) {
            $label = $this->config['tipos'][$tipoKey]['label'] ?? $tipoKey;
            $labels[$label] = $label; // dedup por si dos tipos comparten label
        }
        return array_values($labels);
    }

    /**
     * Label del campo documento para el país, con el nombre que se usa localmente
     * (ej. "RUT" en Chile, "CPF" en Brasil, "Cédula de Identidad"...). Localizado
     * vía documento.campo_por_pais.<abreviacion>; cae a un genérico si no hay
     * entrada. Fuente única del label para registro/perfil/suscribe.
     */
    public function etiquetaCampo($idPais): string
    {
        return $this->etiquetaCampoPorAbreviacion($this->abreviacionDe($idPais));
    }

    public function etiquetaCampoPorAbreviacion(?string $abreviacion): string
    {
        if ($abreviacion !== null) {
            $key = 'documento.campo_por_pais.' . $abreviacion;
            if (\Illuminate\Support\Facades\Lang::has($key)) {
                return __($key);
            }
        }
        return __('documento.campo_generico');
    }

    /**
     * Tipos aceptados (en orden de prioridad) para una abreviación de país. Cae a
     * 'default' si el país no está configurado o la abreviación es null.
     *
     * @return string[]
     */
    private function tiposParaAbreviacion(?string $abreviacion): array
    {
        $porPais = $this->config['paises'] ?? [];
        if ($abreviacion !== null && isset($porPais[$abreviacion])) {
            return $porPais[$abreviacion];
        }
        return $this->config['default'] ?? ['pasaporte'];
    }

    private function abreviacionDe($idPais): ?string
    {
        if (!$idPais) {
            return null;
        }
        if (array_key_exists($idPais, $this->abreviacionCache)) {
            return $this->abreviacionCache[$idPais];
        }
        $pais = Pais::find($idPais);
        $abreviacion = $pais ? $pais->abreviacion : null;
        return $this->abreviacionCache[$idPais] = $abreviacion;
    }

    // -- Normalizadores --------------------------------------------------------

    private function aplicarNormalizador(string $modo, string $valor): string
    {
        switch ($modo) {
            case 'digitos':
                return preg_replace('/\D+/', '', $valor);
            case 'rut':
                // Alnum en mayúsculas: cuerpo (dígitos) + DV (0-9 o K). Se quitan
                // puntos y guiones; la K puede venir en minúscula.
                return preg_replace('/[^0-9K]/', '', strtoupper($valor));
            case 'alnum':
            default:
                return preg_replace('/[^A-Z0-9]/', '', strtoupper($valor));
        }
    }

    // -- Dígitos verificadores -------------------------------------------------

    private function pasaCheck(string $algoritmo, string $valor): bool
    {
        switch ($algoritmo) {
            case 'cpf':
                return $this->checkCpf($valor);
            case 'rut':
                return $this->checkRut($valor);
            default:
                return true; // algoritmo desconocido: no bloquea (solo formato)
        }
    }

    /**
     * CPF (Brasil): 11 dígitos con doble verificador mód. 11. Rechaza además los
     * CPF de dígitos repetidos (00000000000, 11111111111, ...), que pasan el
     * cálculo pero son inválidos.
     */
    private function checkCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $suma = 0;
            for ($i = 0; $i < $t; $i++) {
                $suma += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $dv = ((10 * $suma) % 11) % 10;
            if ($dv !== (int) $cpf[$t]) {
                return false;
            }
        }
        return true;
    }

    /**
     * RUT/RUN (Chile): verificador mód. 11 con multiplicadores cíclicos 2..7
     * sobre el cuerpo, de derecha a izquierda. DV puede ser 0-9 o K.
     */
    private function checkRut(string $rut): bool
    {
        if (!preg_match('/^(\d{7,8})([0-9K])$/', $rut, $m)) {
            return false;
        }
        $cuerpo = $m[1];
        $dvEsperado = $m[2];

        $suma = 0;
        $mult = 2;
        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += (int) $cuerpo[$i] * $mult;
            $mult = $mult === 7 ? 2 : $mult + 1;
        }
        $resto = 11 - ($suma % 11);
        if ($resto === 11) {
            $dvCalc = '0';
        } elseif ($resto === 10) {
            $dvCalc = 'K';
        } else {
            $dvCalc = (string) $resto;
        }
        return $dvCalc === $dvEsperado;
    }
}
