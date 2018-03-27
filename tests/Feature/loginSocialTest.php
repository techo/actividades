<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Socialite;
use Mockery;	
use App\Persona;
use Carbon\Carbon;

class loginSocialTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    public function testFacebookTraerData()
    {
    	$socialite = \Mockery::mock('Laravel\Socialite\Two\User');
    	$socialite->shouldReceive('stateless')->andReturn($socialite);
    	$socialite->shouldReceive('fields')->andReturn($socialite);
    	$socialite->shouldReceive('user')->andReturn($socialite);
    	$socialite->user = array(
    		'first_name' => 'nombre',
    		'last_name' => 'apellido',
    		'email' => 'email',
    		'id' => 'id',
    		'gender' => 'male',
    	);
    	\Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($socialite);
        $response = $this->get('/auth/facebook/callback');
        $response->assertStatus(200);
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->nombre,'nombre');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->apellido,'apellido');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->email,'email');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->facebook_id,'id');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->google_id,'');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->sexo,'M');
    }

    public function testGoogleTraerData()
    {
    	$socialite = \Mockery::mock('Laravel\Socialite\Two\User');
    	$socialite->shouldReceive('stateless')->andReturn($socialite);
    	$socialite->shouldReceive('fields')->andReturn($socialite);
    	$socialite->shouldReceive('user')->andReturn($socialite);
    	$socialite->email = 'email';
    	$socialite->user = array(
    		'name' => ['givenName' => 'nombre', 'familyName' => 'apellido'],
    		'id' => 'id',
    	);
    	\Socialite::shouldReceive('driver')->once()->with('google')->andReturn($socialite);
        $response = $this->get('/auth/google/callback');
        dd($response);
        $response->assertStatus(200);
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->nombre,'nombre');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->apellido,'apellido');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->email,'email');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->facebook_id,'');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->google_id,'id');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->sexo,'');
    }

    public function testRegistroSocialUsuarioExistente() {
    	$persona = Persona::where('mail','aaaa@aaaa.com');
    	if(!$persona) {
    		$persona = new Persona();
	    	$persona->apellidoPaterno = 'apellido';
	    	$persona->dni = 'dni';
	    	$persona->mail = 'email';
	    	$persona->idLocalidad = 1;
	    	$persona->fechaNacimiento = new Carbon();
	    	$persona->nombres = 'nombre';
	    	$persona->idPais = 1;
	    	$persona->idPaisResidencia = 1;
	    	$persona->pasaporte = 'pasaporte';
	    	$persona->password = Hash::make('pass');
	    	$persona->idProvincia = 1;
	    	$persona->sexo = 'F';
	    	$persona->telefonoMovil = 'telefono';
	    	$persona->carrera = '';
	    	$persona->anoEstudio = '';
	    	$persona->idContactoCTCT = '';
	    	$persona->statusCTCT = '';
	    	$persona->lenguaje = '';
	    	$persona->idRegionLT = 0;
	    	$persona->idUnidadOrganizacional = 0;
	    	$persona->idCiudad = 0;
	    	$persona->save();
    	}
    	$socialite = \Mockery::mock('Laravel\Socialite\Two\User');
    	$socialite->shouldReceive('stateless')->andReturn($socialite);
    	$socialite->shouldReceive('fields')->andReturn($socialite);
    	$socialite->shouldReceive('user')->andReturn($socialite);
    	$socialite->email = 'email';
    	$socialite->user = array(
    		'name' => ['givenName' => 'nombre', 'familyName' => 'apellido'],
    		'id' => 'id',
    	);
    	\Socialite::shouldReceive('driver')->once()->with('google')->andReturn($socialite);
        $response = $this->get('/auth/google/callback');
        $response->assertStatus(200);
    	$this->assertTrue(Auth::check());
    }
}
