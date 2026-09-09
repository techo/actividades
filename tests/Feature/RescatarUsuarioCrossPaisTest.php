<?php

namespace Tests\Feature;

use App\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Política híbrida de /admin/usuarios: un admin acotado a un país puede ENCONTRAR por
 * email exacto y RESCATAR (ver/editar) a personas "varadas" (país por defecto o país sin
 * coordinación), pero NO puede tocar a personas de otro país habilitado (otra coordinación).
 */
class RescatarUsuarioCrossPaisTest extends TestCase
{
    use RefreshDatabase;

    private $paisAdmin;
    private $paisOtroActivo;
    private $paisDefault;
    private $paisHuerfano;

    private function montarPaises()
    {
        $this->paisAdmin      = factory('App\Pais')->create(['habilitado' => 1]);
        $this->paisOtroActivo = factory('App\Pais')->create(['habilitado' => 1]);
        $this->paisDefault    = factory('App\Pais')->create(['habilitado' => 1]);
        $this->paisHuerfano   = factory('App\Pais')->create(['habilitado' => 0]);

        // El país "genérico por defecto" donde caen los registros que agarraron el default.
        config(['app.pais_default' => $this->paisDefault->id]);
    }

    private function adminDelPais($pais)
    {
        $this->seed('PermisosSeeder');
        $admin = factory('App\Persona')->create(['idPais' => $pais->id]);
        $admin->idPaisPermitido = $pais->id; // acotado a un país (NO admin global)
        $admin->save();
        $admin->assignRole('admin');
        return $admin;
    }

    private function datosEdicion(Persona $p)
    {
        return [
            'idUsuario'  => $p->idPersona,
            'nombre'     => $p->nombres,
            'apellido'   => $p->apellidoPaterno,
            'pais'       => ['id' => $p->idPais],
            'genero'     => $p->genero,
            'nacimiento' => $p->fechaNacimiento,
            'telefono'   => $p->telefonoMovil,
            'dni'        => $p->dni,
            'email'      => $p->mail,
            'rol'        => ['rol' => 'usuario_autenticado'],
            'password'   => 'contraseña',
            'password_confirmation' => 'contraseña',
        ];
    }

    private function editar($admin, Persona $p)
    {
        return $this->actingAs($admin)
            ->post('/admin/usuarios/' . $p->idPersona . '/editar', $this->datosEdicion($p));
    }

    /** @test */
    public function puede_editar_persona_de_su_propio_pais()
    {
        $this->montarPaises();
        $admin  = $this->adminDelPais($this->paisAdmin);
        $persona = factory('App\Persona')->create(['idPais' => $this->paisAdmin->id]);

        $this->editar($admin, $persona)->assertStatus(200);
    }

    /** @test */
    public function no_puede_editar_persona_de_otro_pais_habilitado()
    {
        $this->montarPaises();
        $admin  = $this->adminDelPais($this->paisAdmin);
        $ajena  = factory('App\Persona')->create(['idPais' => $this->paisOtroActivo->id]);

        $this->editar($admin, $ajena)->assertStatus(403);
    }

    /** @test */
    public function puede_rescatar_persona_del_pais_por_defecto()
    {
        $this->montarPaises();
        $admin    = $this->adminDelPais($this->paisAdmin);
        $varada   = factory('App\Persona')->create(['idPais' => $this->paisDefault->id]);

        $this->editar($admin, $varada)->assertStatus(200);
    }

    /** @test */
    public function puede_rescatar_persona_de_pais_no_habilitado()
    {
        $this->montarPaises();
        $admin    = $this->adminDelPais($this->paisAdmin);
        $huerfana = factory('App\Persona')->create(['idPais' => $this->paisHuerfano->id]);

        $this->editar($admin, $huerfana)->assertStatus(200);
    }

    /** @test */
    public function la_busqueda_por_email_exacto_encuentra_gente_de_otro_pais_pero_por_nombre_no()
    {
        $this->montarPaises();
        $admin = $this->adminDelPais($this->paisAdmin);
        $ajena = factory('App\Persona')->create([
            'idPais'  => $this->paisOtroActivo->id,
            'nombres' => 'ZzUnicoNombreDeOtroPais',
        ]);

        // Email exacto -> rescate: la encuentra aunque sea de otro país.
        $this->actingAs($admin)
            ->get('/admin/ajax/search/usuarios?usuario=' . urlencode($ajena->mail))
            ->assertStatus(200)
            ->assertJsonFragment(['idPersona' => $ajena->idPersona]);

        // Por nombre -> sigue acotado al país del admin: NO la trae.
        $this->actingAs($admin)
            ->get('/admin/ajax/search/usuarios?usuario=ZzUnicoNombreDeOtroPais')
            ->assertStatus(200)
            ->assertJsonMissing(['idPersona' => $ajena->idPersona]);
    }
}
