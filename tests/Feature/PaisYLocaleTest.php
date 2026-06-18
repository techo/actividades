<?php

namespace Tests\Feature;

use App\ActividadFactory;
use App\Services\Push\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * Tests de CARACTERIZACIÓN del subsistema multi-país + idioma.
 *
 * No describen el comportamiento "ideal", sino el ACTUAL — incluidas sus
 * rarezas e inconsistencias — para que sirvan de red de seguridad antes de
 * refactorizar/unificar la lógica de país y locale.
 *
 * Mapa (al 2026-06-17):
 *  - País activo: vive en Session('pais') y se refleja en config('app.pais').
 *  - Locale: vive en Session('locale'), se aplica con App::setLocale (mw Localization),
 *    y se deriva del locale del país (atl_pais.locale, default es_AR).
 *  - Dos capas de traducción: strings de UI (resources/lang, distingue es_AR/es_CH)
 *    y contenido (Tipo.nombre/_pt/_en, solo distingue es/pt/en).
 */
class PaisYLocaleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function seleccionar_pais_guarda_pais_y_locale_en_sesion_y_redirige_a_la_abreviacion()
    {
        $pais = factory('App\Pais')->create([
            'abreviacion' => 'ar',
            'locale'      => 'es_AR',
            'habilitado'  => 1,
        ]);

        $this->get('/seleccionar-pais/' . $pais->id)
            ->assertRedirect('ar')
            ->assertSessionHas('pais', $pais->id)
            ->assertSessionHas('pais_abreviacion', 'ar')
            ->assertSessionHas('locale', 'es_AR');
    }

    /** @test */
    public function deseleccionar_pais_limpia_el_pais_de_la_sesion()
    {
        $pais = factory('App\Pais')->create([ 'habilitado' => 1 ]);

        $this->withSession([ 'pais' => $pais->id, 'pais_abreviacion' => $pais->abreviacion ])
            ->get('/deseleccionar-pais')
            ->assertRedirect('/actividades')
            ->assertSessionMissing('pais');
    }

    /** @test */
    public function el_listado_de_actividades_filtra_por_el_pais_activo_de_la_sesion()
    {
        $this->seed('PermisosSeeder');

        $argentina = factory('App\Pais')->create();
        $brasil    = factory('App\Pais')->create();

        app(ActividadFactory::class)->conPais($argentina->id)->agregarPuntoConInscriptos(0)->create();
        app(ActividadFactory::class)->conPais($brasil->id)->agregarPuntoConInscriptos(0)->create();

        // Con país activo en sesión, el middleware SeleccionarPais lo copia a
        // config('app.pais') y ActividadesSearch filtra por él.
        $this->withSession([ 'pais' => $argentina->id ])
            ->get('/ajax/actividades')
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function al_loguearse_el_locale_se_toma_del_pais_del_usuario()
    {
        $this->seed('PermisosSeeder');

        $paisBrasil = factory('App\Pais')->create([ 'locale' => 'pt' ]);
        $persona    = factory('App\Persona')->create([ 'idPais' => $paisBrasil->id ]);

        $this->post('/login', [ 'mail' => $persona->mail, 'password' => 'password' ])
            ->assertStatus(200)
            ->assertSessionHas('locale', 'pt')
            ->assertSessionHas('pais', $paisBrasil->id);
    }

    /**
     * El contenido (Tipo) solo distingue pt / en / español-base.
     * Cualquier locale español (es_AR, es_CH, es) cae al nombre base.
     * @test
     */
    public function el_nombre_de_tipo_se_traduce_solo_para_pt_y_en()
    {
        $tipo = factory('App\Tipo')->create([
            'nombre'    => 'Construcción',
            'nombre_pt' => 'Construção',
            'nombre_en' => 'Construction',
        ]);

        App::setLocale('pt');
        $this->assertEquals('Construção', $tipo->fresh()->nombre_localizado);

        App::setLocale('en');
        $this->assertEquals('Construction', $tipo->fresh()->nombre_localizado);

        // es_AR y es_CH NO tienen bucket propio de contenido: caen al nombre base.
        App::setLocale('es_AR');
        $this->assertEquals('Construcción', $tipo->fresh()->nombre_localizado);

        App::setLocale('es_CH');
        $this->assertEquals('Construcción', $tipo->fresh()->nombre_localizado);
    }

    /**
     * En cambio, los strings de UI SÍ distinguen es_AR de es_CH (voseo).
     * Esto documenta la inconsistencia entre las dos capas de traducción.
     * @test
     */
    public function los_strings_de_ui_distinguen_es_ar_de_es_ch()
    {
        App::setLocale('es_AR');
        $ar = __('frontend.last_step_waiting_for_confirmation');

        App::setLocale('es_CH');
        $ch = __('frontend.last_step_waiting_for_confirmation');

        $this->assertNotEquals($ar, $ch);
        $this->assertContains('Quedás', $ar); // voseo argentino
    }

    /**
     * El push se traduce al idioma del país del usuario y DEBE restaurar el
     * locale previo del request (regresión de la fuga de locale corregida en
     * PushNotificationService::enviarLocalizado).
     * @test
     */
    public function enviar_push_localizado_restaura_el_locale_previo()
    {
        $paisBrasil = factory('App\Pais')->create([ 'locale' => 'pt' ]);
        // recibir_push = 0 (default) → enviar() corta antes de llamar a OneSignal.
        $persona = factory('App\Persona')->create([ 'idPais' => $paisBrasil->id ]);

        App::setLocale('en');

        app(PushNotificationService::class)->enviarLocalizado(
            $persona,
            'push.pre_inscripto_titulo',
            'push.pre_inscripto_cuerpo',
            ['actividad' => 'X']
        );

        $this->assertEquals('en', App::getLocale());
    }
}
