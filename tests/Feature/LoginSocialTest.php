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

        $this->put('/ajax/usuario/linkear', [
                'email' => 'aaaa@aaaa.com', 
                'media' => 'facebook', 
                'id' => '1234567890'
        ])->assertStatus(200);

        $this->assertAuthenticatedAs($persona);
        $this->assertDatabaseHas('Persona', [ 'facebook_id' => '1234567890']);
    }

}
