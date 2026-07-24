<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Socialite;
use Mockery;

class loginSocialTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function usuario_existente_inicia_sesion() {
    	$this->withSession(['login_callback' => 'url_callback']);

    	$persona = factory('App\Persona')->create([
            'mail' => 'aaaa@aaaa.com',
            'google_id' => 'id'
        ]);

    	$socialite = \Mockery::mock('Laravel\Socialite\Two\User');
    	$socialite->shouldReceive('stateless')->andReturn($socialite);
    	$socialite->shouldReceive('fields')->andReturn($socialite);
    	$socialite->shouldReceive('user')->andReturn($socialite);
    	$socialite->email = 'aaaa@aaaa.com';
    	$socialite->user = array(
    		'given_name' => 'nombre', 
            'family_name' => 'apellido',
    		'id' => 'id',
    	);
    	\Socialite::shouldReceive('driver')->once()->with('google')->andReturn($socialite);
        
        $this->get('/auth/google/callback')->assertStatus(302);
    	$this->assertAuthenticatedAs($persona);
    }

    /** @test */
    public function usuario_existente_vincula_cuenta_de_google() {
        $this->withSession(['login_callback' => 'url_callback']);
        
        $persona = factory('App\Persona')->create([
            'mail' => 'aaaa@aaaa.com'
        ]);

        $socialite = \Mockery::mock('Laravel\Socialite\Two\User');
        $socialite->shouldReceive('stateless')->andReturn($socialite);
        $socialite->shouldReceive('fields')->andReturn($socialite);
        $socialite->shouldReceive('user')->andReturn($socialite);
        $socialite->email = 'aaaa@aaaa.com';
        $socialite->user = array(
            'given_name' => 'nombre', 
            'family_name' => 'apellido',
            'id' => 'id',
        );
        \Socialite::shouldReceive('driver')->once()->with('google')->andReturn($socialite);
        $response = $this->get('/auth/google/callback')->assertStatus(200);

        $this->assertEquals($response->getOriginalContent()->getData()['persona']->google_id,'id');
        $this->assertEquals($response->getOriginalContent()->getData()['linkear'],true);
    }

    /** @test */
    public function usuario_existente_vincula_cuenta_de_facebook() {
        $this->withSession(['login_callback' => 'url_callback']);
        
        $persona = factory('App\Persona')->create([
            'mail' => 'aaaa@aaaa.com'
        ]);

        $socialite = \Mockery::mock('Laravel\Socialite\Two\User');
        $socialite->shouldReceive('stateless')->andReturn($socialite);
        $socialite->shouldReceive('fields')->andReturn($socialite);
        $socialite->shouldReceive('user')->andReturn($socialite);
        $socialite->user = array(
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'email' => 'aaaa@aaaa.com',
            'id' => 'id',
            'gender' => 'male',
        );
        \Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($socialite);
        $response = $this->get('/auth/facebook/callback')->assertStatus(200);

        $this->assertEquals($response->getOriginalContent()->getData()['persona']->facebook_id,'id');
        $this->assertEquals($response->getOriginalContent()->getData()['linkear'],true);
    }

    /** @test */
    public function usuario_existente_confirma_vinculacion_con_google() {

        $persona = factory('App\Persona')->create([
            'mail' => 'aaaa@aaaa.com'
        ]);

        // El callback OAuth deja en sesión los datos verificados por el proveedor.
        // linkear usa ESTOS valores, no los del request.
        $this->withSession(['link_social' => [
            'email'     => 'aaaa@aaaa.com',
            'provider'  => 'google',
            'social_id' => '1234567890',
        ]]);

        $this->put('/ajax/usuario/linkear', [
            'email' => 'aaaa@aaaa.com',
            'media' => 'google',
            'id' => '1234567890'
        ])->assertStatus(200);

        $this->assertAuthenticatedAs($persona);
        $this->assertDatabaseHas('Persona', [ 'google_id' => '1234567890']);
    }

    /** @test */
    public function usuario_existente_confirma_vinculacion_con_facebook() {

        $persona = factory('App\Persona')->create([
            'mail' => 'aaaa@aaaa.com'
        ]);

        $this->withSession(['link_social' => [
            'email'     => 'aaaa@aaaa.com',
            'provider'  => 'facebook',
            'social_id' => '1234567890',
        ]]);

        $this->put('/ajax/usuario/linkear', [
                'email' => 'aaaa@aaaa.com',
                'media' => 'facebook',
                'id' => '1234567890'
        ])->assertStatus(200);

        $this->assertAuthenticatedAs($persona);
        $this->assertDatabaseHas('Persona', [ 'facebook_id' => '1234567890']);
    }

    /**
     * Regresión C-1: sin datos verificados en sesión (que solo setea el callback OAuth),
     * un atacante NO puede loguearse ni linkear una cuenta enviando un email arbitrario.
     *
     * @test
     */
    public function linkear_sin_sesion_verificada_no_autentica_ni_linkea() {

        $victima = factory('App\Persona')->create([
            'mail' => 'victima@techo.org'
        ]);

        $response = $this->put('/ajax/usuario/linkear', [
            'email' => 'victima@techo.org',
            'media' => 'google',
            'id' => 'id-del-atacante'
        ])->assertStatus(200);

        $this->assertGuest();
        $this->assertFalse($response->json('success'));
        $this->assertDatabaseMissing('Persona', ['google_id' => 'id-del-atacante']);
    }

    /**
     * Regresión C-1: el email del request se ignora; se usa el de la sesión verificada.
     * Aunque el body apunte a la víctima, solo se opera sobre el email verificado por OAuth.
     *
     * @test
     */
    public function linkear_ignora_el_email_del_request() {

        $victima = factory('App\Persona')->create([
            'mail' => 'victima@techo.org'
        ]);

        // Sesión verificada de un email SIN persona asociada.
        $this->withSession(['link_social' => [
            'email'     => 'atacante@evil.com',
            'provider'  => 'google',
            'social_id' => 'x',
        ]]);

        $response = $this->put('/ajax/usuario/linkear', [
            'email' => 'victima@techo.org',   // el atacante intenta apuntar a la víctima
            'media' => 'google',
            'id' => 'x'
        ])->assertStatus(200);

        $this->assertGuest();
        $this->assertFalse($response->json('success'));
        $this->assertDatabaseMissing('Persona', ['google_id' => 'x']);
    }

}
