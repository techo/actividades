<?php

namespace Tests\Feature;

use App\ActividadFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 53 — acceso al link de evaluación (reclamos #5/#12).
 */
class EvaluacionAccesoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function sin_sesion_vuelve_a_la_evaluacion_aunque_venga_de_un_webmail()
    {
        $actividad = app(ActividadFactory::class)->conEstado('pasada')->create();
        $url = url('/actividades/' . $actividad->idActividad . '/evaluaciones');

        $response = $this->get($url, ['Referer' => 'https://mail.google.com/mail/u/0/'])
            ->assertRedirect('/login');

        $cookie = collect($response->headers->getCookies())->first(function ($c) {
            return $c->getName() === 'after_login_url';
        });
        $this->assertNotNull($cookie);
        $this->assertEquals($url, decrypt($cookie->getValue(), false));
    }

    /** @test */
    public function un_no_presente_ve_un_mensaje_que_explica_por_que_no_puede_evaluar()
    {
        $this->seed('PermisosSeeder');
        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conGrupoRaiz()
            ->conEstado('pasada')
            ->create();
        $maria = factory('App\Persona')->create();
        factory('App\Inscripcion')->create([
            'idActividad' => $actividad->idActividad,
            'idPersona' => $maria->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
        ]);

        $this->actingAs($maria)
            ->get('/actividades/' . $actividad->idActividad . '/evaluaciones')
            ->assertStatus(403)
            ->assertSee(e(__('errors.evaluar.no_presente')));
    }

    /** @test */
    public function los_demas_403_siguen_con_el_mensaje_generico()
    {
        $this->seed('PermisosSeeder');
        $actividad = app(ActividadFactory::class)->create();
        $persona = factory('App\Persona')->create();

        $this->actingAs($persona)
            ->get('/admin/actividades/' . $actividad->idActividad . '/grupos')
            ->assertSee(e(__('errors.e403.message')));
    }

    /** @test */
    public function el_link_del_mail_evaluar_vuelve_a_la_evaluacion_tras_el_login()
    {
        // /evaluar/{id} existe para que la app (que captura /actividades/*) no se lo lleve.
        $actividad = app(ActividadFactory::class)->conEstado('pasada')->create();
        $url = url('/evaluar/' . $actividad->idActividad);

        $response = $this->get($url)->assertRedirect('/login');

        $cookie = collect($response->headers->getCookies())->first(function ($c) {
            return $c->getName() === 'after_login_url';
        });
        $this->assertEquals($url, decrypt($cookie->getValue(), false));
    }

    /** @test */
    public function el_mail_de_invitacion_apunta_a_evaluar_y_no_a_actividades()
    {
        $html = file_get_contents(resource_path('views/emails/invitacionEvaluacion.blade.php'));
        $this->assertContains("url('/evaluar/'", $html);
        $this->assertNotContains("/evaluaciones')", $html);
    }
}
