<?php

namespace App\Rules;

use App\Services\Documento\DocumentoService;
use Illuminate\Contracts\Validation\Rule;

/**
 * Regla de validación del documento de identidad según el país. Es la fuente
 * única de validación (registro web, alta/edición móvil, perfil): delega en
 * App\Services\Documento\DocumentoService, que a su vez usa config/documentos.php.
 *
 * Uso: 'dni' => ['nullable', new DocumentoValido($request->pais)]              // web, auto-detect
 *      'dni' => ['required', new DocumentoValido($this->idPais)]               // FormRequest
 *      'dni' => ['required', new DocumentoValido($request->pais, $request->tipo_documento)] // selector explícito
 *
 * Con 'nullable' delante, Laravel saltea la regla si el valor viene vacío; con
 * 'required' exige presencia. La regla en sí acepta vacío (la presencia no es su
 * responsabilidad), así que compone bien con ambas.
 *
 * Con $tipo, la validación es ESTRICTA contra ese único tipo (no cae a pasaporte)
 * y además se exige que el tipo sea legítimo para el país. Sin $tipo, mantiene el
 * auto-detect histórico (prueba los tipos aceptados del país en orden).
 */
class DocumentoValido implements Rule
{
    /** @var int|string|null */
    private $idPais;

    /** @var string|null */
    private $tipo;

    /** @var DocumentoService */
    private $service;

    public function __construct($idPais, $tipo = null, DocumentoService $service = null)
    {
        $this->idPais = $idPais;
        $this->tipo = $tipo !== null && $tipo !== '' ? (string) $tipo : null;
        $this->service = $service ?? new DocumentoService();
    }

    public function passes($attribute, $value)
    {
        if ($this->tipo !== null) {
            return $this->service->esValidoComoTipo($this->tipo, $value, $this->idPais);
        }
        return $this->service->esValido($this->idPais, $value);
    }

    public function message()
    {
        // Con tipo elegido, el error nombra ese tipo; sin tipo, la lista del país.
        if ($this->tipo !== null && $this->service->tipoEsValidoParaPais($this->tipo, $this->idPais)) {
            $tipos = [__('documento.tipo.' . $this->labelDeTipo($this->tipo))];
        } else {
            $tipos = array_map(function ($labelKey) {
                return __('documento.tipo.' . $labelKey);
            }, $this->service->labelsPara($this->idPais));
        }

        return __('documento.invalido', ['tipos' => $this->listarTipos($tipos)]);
    }

    private function labelDeTipo(string $tipoKey): string
    {
        $tipos = (array) config('documentos.tipos', []);
        return $tipos[$tipoKey]['label'] ?? $tipoKey;
    }

    /**
     * "DNI", "DNI o pasaporte", "CPF, cédula o pasaporte", según la cantidad.
     */
    private function listarTipos(array $tipos): string
    {
        if (count($tipos) <= 1) {
            return $tipos[0] ?? __('documento.tipo.documento');
        }
        $ultimo = array_pop($tipos);
        return implode(', ', $tipos) . ' ' . __('documento.o') . ' ' . $ultimo;
    }
}
