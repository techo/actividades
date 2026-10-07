<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\IssueReport;
use Illuminate\Support\Facades\Storage;

/**
 * Bandeja de reportes de problemas / sugerencias (backoffice, solo admin).
 * El intake (widget) vive en backoffice\ajax\ReportesController.
 */
class ReportesController extends Controller
{
    public function index()
    {
        $config    = config('datatables.reportes');
        $fields    = json_encode($config['fields']);
        $sortOrder = json_encode($config['sortOrder']);

        return view('backoffice.reportes.index', compact('fields', 'sortOrder'));
    }

    /**
     * Sirve la captura de pantalla privada de un reporte (inline, autenticado).
     * Las imágenes viven en storage/app (disco privado), no son accesibles por URL directa.
     */
    public function captura($id)
    {
        $report = IssueReport::findOrFail($id);

        abort_unless($report->screenshot_path && Storage::exists($report->screenshot_path), 404);

        return Storage::response($report->screenshot_path);
    }
}
