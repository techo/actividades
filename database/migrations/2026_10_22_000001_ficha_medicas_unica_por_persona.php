<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Una ficha médica por persona.
 *
 * El alta de ficha (ajax/FichaMedicaController) contaba y después creaba, sin lock: dos
 * envíos casi simultáneos dejaban dos o tres fichas para la misma persona, y el listado
 * de inscripciones (LEFT JOIN ficha_medicas) mostraba a esa persona repetida en TODAS
 * sus actividades (reclamo #31, oct-2026: 4 personas, 5 fichas de más).
 *
 * Se conserva la ficha más completa (más campos cargados; empate → la más reciente), se
 * borran las demás (quedan en el log por si hay que recuperarlas) y se agrega UNIQUE.
 */
class FichaMedicasUnicaPorPersona extends Migration
{
    const CAMPOS = [
        'contacto_nombre', 'contacto_telefono', 'contacto_relacion', 'grupo_sanguinieo',
        'cobertura_tipo', 'cobertura_nombre', 'cobertura_numero', 'alergias', 'vacunacion_covid',
        'alimentacion', 'enfermedades_preexistentes', 'archivo_medico', 'documento_frente',
        'documento_dorso', 'confirma_datos',
    ];

    public function up()
    {
        $campos = array_values(array_filter(self::CAMPOS, function ($campo) {
            return Schema::hasColumn('ficha_medicas', $campo);
        }));

        $duplicadas = DB::table('ficha_medicas')
            ->select('idPersona')
            ->groupBy('idPersona')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('idPersona');

        $respaldo = [];
        foreach ($duplicadas as $idPersona) {
            $fichas = DB::table('ficha_medicas')->where('idPersona', $idPersona)->get();

            $conservar = $fichas->sortByDesc(function ($ficha) use ($campos) {
                $cargados = collect($campos)->filter(function ($campo) use ($ficha) {
                    return $ficha->{$campo} !== null && trim((string) $ficha->{$campo}) !== '';
                })->count();

                // Más completa primero; a igual completitud, la más reciente.
                return sprintf('%03d-%010d', $cargados, $ficha->idFichaMedica);
            })->first();

            $borrar = $fichas->where('idFichaMedica', '!=', $conservar->idFichaMedica);
            $respaldo[] = [
                'idPersona' => $idPersona,
                'conservada' => $conservar->idFichaMedica,
                'borradas' => $borrar->values()->all(),
            ];

            DB::table('ficha_medicas')->whereIn('idFichaMedica', $borrar->pluck('idFichaMedica'))->delete();
        }

        if ($respaldo) {
            file_put_contents(
                storage_path('app/ficha_medicas_duplicadas_borradas.json'),
                json_encode($respaldo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }

        Schema::table('ficha_medicas', function (Blueprint $table) {
            $table->unique('idPersona', 'ficha_medicas_idpersona_unique');
        });
    }

    public function down()
    {
        Schema::table('ficha_medicas', function (Blueprint $table) {
            $table->dropUnique('ficha_medicas_idpersona_unique');
        });
    }
}
