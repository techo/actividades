<?php

namespace Tests\Feature;

use App\Actividad;
use App\ActividadFactory;
use App\Grupo;
use App\GrupoRolPersona;
use App\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reporte #22: actividades con grupos raíz duplicados (clonadas/renombradas antes del fix de
 * oct-2026) mostraban en la columna "Grupo" el nombre de otra actividad.
 */
class FusionarRaicesGruposTest extends TestCase
{
    use RefreshDatabase;

    private function actividad($nombre)
    {
        return app(ActividadFactory::class)->create(['nombreActividad' => $nombre, 'fechaInicio' => now()->addMonth()]);
    }

    private function raiz(Actividad $a, $nombre, $link = null)
    {
        return factory(Grupo::class)->create([
            'idActividad' => $a->idActividad, 'idPadre' => 0, 'nombre' => $nombre, 'linkEvaluacion' => $link,
        ]);
    }

    private function miembro(Actividad $a, Grupo $g, Persona $p = null)
    {
        $p = $p ?: factory(Persona::class)->create();
        GrupoRolPersona::create(['idActividad' => $a->idActividad, 'idPersona' => $p->idPersona, 'idGrupo' => $g->idGrupo, 'rol' => '']);
        return $p;
    }

    /** @test */
    public function fusiona_raices_duplicadas_en_la_mas_antigua_y_la_renombra()
    {
        $a = $this->actividad('[TETO RS] CC2611 | Vila Nazaré');
        $vieja = $this->raiz($a, 'CC2603-RJ | Aldeia Mata Verde Bonita');
        $nueva = $this->raiz($a, '[TETO RS] CC2611 | Vila Nazaré', 'https://forms.example/eval');
        $sub = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $nueva->idGrupo, 'nombre' => 'Cuadrilla 1']);

        $enVieja = $this->miembro($a, $vieja);
        $enNueva = $this->miembro($a, $nueva);
        $enAmbas = $this->miembro($a, $vieja);
        $this->miembro($a, $nueva, $enAmbas);
        $enSub = $this->miembro($a, $sub);

        // Otra actividad con raíz duplicada: con --actividad no se toca.
        $b = $this->actividad('Otra');
        $this->raiz($b, 'Otra');
        $this->raiz($b, 'Otra bis');

        $this->artisan('grupos:fusionar-raices', ['--actividad' => $a->idActividad, '--commit' => true])->assertExitCode(0);

        $raices = Grupo::where('idActividad', $a->idActividad)->where('idPadre', 0)->get();
        $this->assertCount(1, $raices);
        $this->assertEquals($vieja->idGrupo, $raices[0]->idGrupo);
        $this->assertEquals('[TETO RS] CC2611 | Vila Nazaré', $raices[0]->nombre);
        $this->assertEquals('https://forms.example/eval', $raices[0]->linkEvaluacion);

        foreach ([$enVieja, $enNueva, $enAmbas] as $p) {
            $this->assertEquals(1, GrupoRolPersona::where('idActividad', $a->idActividad)->where('idPersona', $p->idPersona)->count());
            $this->assertDatabaseHas('Grupo_Persona', ['idActividad' => $a->idActividad, 'idPersona' => $p->idPersona, 'idGrupo' => $vieja->idGrupo]);
        }
        $this->assertDatabaseHas('Grupo', ['idGrupo' => $sub->idGrupo, 'idPadre' => $vieja->idGrupo]);
        $this->assertDatabaseHas('Grupo_Persona', ['idPersona' => $enSub->idPersona, 'idGrupo' => $sub->idGrupo]);

        $this->assertEquals(2, Grupo::where('idActividad', $b->idActividad)->where('idPadre', 0)->count());
    }

    /** @test */
    public function sin_commit_no_modifica_nada()
    {
        $a = $this->actividad('Nueva');
        $vieja = $this->raiz($a, 'Copia de Vieja');
        $nueva = $this->raiz($a, 'Nueva');
        $p = $this->miembro($a, $nueva);

        $this->artisan('grupos:fusionar-raices', ['--actividad' => $a->idActividad])->assertExitCode(0);

        $this->assertEquals(2, Grupo::where('idActividad', $a->idActividad)->where('idPadre', 0)->count());
        $this->assertDatabaseHas('Grupo', ['idGrupo' => $vieja->idGrupo, 'nombre' => 'Copia de Vieja']);
        $this->assertDatabaseHas('Grupo_Persona', ['idPersona' => $p->idPersona, 'idGrupo' => $nueva->idGrupo]);
    }

    /** @test */
    public function renombrar_la_actividad_renombra_su_grupo_raiz()
    {
        $a = $this->actividad('Copia de Construcción marzo');
        $raiz = $this->raiz($a, 'Copia de Construcción marzo');
        $sub = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $raiz->idGrupo, 'nombre' => 'Cuadrilla 1']);

        $a->nombreActividad = 'Construcción noviembre';
        $a->save();

        $this->assertDatabaseHas('Grupo', ['idGrupo' => $raiz->idGrupo, 'nombre' => 'Construcción noviembre']);
        $this->assertDatabaseHas('Grupo', ['idGrupo' => $sub->idGrupo, 'nombre' => 'Cuadrilla 1']);
    }
}
