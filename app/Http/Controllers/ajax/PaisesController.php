<?php

namespace App\Http\Controllers\ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Pais;
use App\Provincia;
use App\Services\Documento\DocumentoService;

class PaisesController extends Controller
{
    public function index(Request $request)
    {
        // Nota: la ruta pública `ajax/paises` (dropdown de país en registro/perfil)
        // resuelve a ESTE método (route:list manda; la def de `@paises` quedó
        // shadoweada). Por eso el documento_label se agrega también acá.
        return $this->conDocumentoLabel(Pais::orderBy('nombre')->get());
    }

    public function paises(Request $request)
    {
        return $this->conDocumentoLabel(Pais::has('actividades')->orderBy('nombre')->get());
    }

    /**
     * Agrega a cada país el label del campo documento ("RUT" en Chile, "CPF" en
     * Brasil...) y las opciones del selector de tipo (key + label), para que el
     * front los muestre sin lógica duplicada. Fuente: DocumentoService
     * (config/documentos.php + i18n).
     */
    private function conDocumentoLabel($paises)
    {
        $doc = new DocumentoService();
        foreach ($paises as $pais) {
            $pais->documento_label = $doc->etiquetaCampoPorAbreviacion($pais->abreviacion);
            // Orden de prioridad: el primero es el tipo por defecto del país.
            $pais->documento_tipos = $doc->opcionesTiposPorAbreviacion($pais->abreviacion);
        }
        return $paises;
    }

    public function provincias($id_pais) {
    	return Pais::find($id_pais)->provincias;
    }

    public function localidades($id_pais, $id_provincia) {
    	return Provincia::find($id_provincia)->localidades;
    }

    public function paisesHabilitados() {
        return Pais::where('habilitado', true)->get();
    }

    public function paisesConInstitucionesEducativas(Request $request)
    {
        return Pais::has('institucionEducativa')->orderBy('nombre')->get();
    }

    public function paisesPropios() {
        return Pais::where('id', '=', auth()->user()->idPaisPermitido)->get();
    }

}
