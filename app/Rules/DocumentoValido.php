<?php

namespace App\Rules;

use App\Services\Documento\DocumentoService;
use Illuminate\Contracts\Validation\Rule;

/**
 * Regla de validación del documento de identidad según el país. Es la fuente
 * única de validación (registro web, alta/edición móvil, perfil): delega en
 * App\Services\Documento\DocumentoService, que a su vez usa config/documentos.php.
 *
 * Uso: 'dni' => ['nullable', new DocumentoValido($request->pais)]  // web
 *      'dni' => ['required', new DocumentoValido($this->idPais)]   // FormRequest
 *
 * Con 'nullable' delante, Laravel saltea la regla si el valor viene vacío; con
 * 'required' exige presencia. La regla en sí acepta vacío (la presencia no es su
 * responsabilidad), así que compone bien con ambas.
 */
class DocumentoValido implements Rule
{
    /** @var int|string|null */
    private $idPais;

    /** @var DocumentoService */
    private $service;

    public function __construct($idPais, DocumentoService $service = null)
    {
        $this->idPais = $idPais;
        $this->service = $service ?? new DocumentoService();
    }

    public function passes($attribute, $value)
    {
        return $this->service->esValido($this->idPais, $value);
    }

    public function message()
    {
        $tipos = array_map(function ($labelKey) {
            return __('documento.tipo.' . $labelKey);
        }, $this->service->labelsPara($this->idPais));

        return __('documento.invalido', ['tipos' => $this->listarTipos($tipos)]);
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
