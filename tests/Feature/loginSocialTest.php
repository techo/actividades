<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Socialite;
use Mockery;
use App\Persona;
use Carbon\Carbon;

class loginSocialTest extends TestCase
{
    use DatabaseTransactions;

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
        $response->assertStatus(200);
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->nombre,'nombre');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->apellido,'apellido');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->email,'email');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->facebook_id,'');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->google_id,'id');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->sexo,'');
    }

    public function testRegistroSocialUsuarioExistente() {
    	$this->withSession(['login_callback' => 'url_callback']);
    	$persona = Persona::where('mail','aaaa@aaaa.com')->first();
    	if(!$persona) {
    		$persona = new Persona();
	    	$persona->apellidoPaterno = 'apellido';
	    	$persona->dni = 'dni';
	    	$persona->mail = 'aaaa@aaaa.com';
	    	$persona->idLocalidad = 1;
	    	$persona->fechaNacimiento = new Carbon();
	    	$persona->nombres = 'nombre';
	    	$persona->idPais = 1;
	    	$persona->idPaisResidencia = 1;
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
	    	$persona->google_id = 'id';
	    	$persona->save();
    	}
    	$socialite = \Mockery::mock('Laravel\Socialite\Two\User');
    	$socialite->shouldReceive('stateless')->andReturn($socialite);
    	$socialite->shouldReceive('fields')->andReturn($socialite);
    	$socialite->shouldReceive('user')->andReturn($socialite);
    	$socialite->email = 'aaaa@aaaa.com';
    	$socialite->user = array(
    		'name' => ['givenName' => 'nombre', 'familyName' => 'apellido'],
    		'id' => 'id',
    	);
    	\Socialite::shouldReceive('driver')->once()->with('google')->andReturn($socialite);
        $response = $this->get('/auth/google/callback');
        $response->assertStatus(302);
    	$this->assertTrue(Auth::check());
    }

    public function testLoginSocialUsuarioExistentePeroNoRelacionadoALaRedSocial() {
        $this->withSession(['login_callback' => 'url_callback']);
        $persona = Persona::where('mail','aaaa@aaab.com')->first();
        if(!$persona) {
            $persona = new Persona();
            $persona->apellidoPaterno = 'apellido';
            $persona->dni = 'dni';
            $persona->mail = 'aaaa@aaab.com';
            $persona->idLocalidad = 1;
            $persona->fechaNacimiento = new Carbon();
            $persona->nombres = 'nombre';
            $persona->idPais = 1;
            $persona->idPaisResidencia = 1;
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
            $persona->google_id = '';
            $persona->save();
        }
        $socialite = \Mockery::mock('Laravel\Socialite\Two\User');
        $socialite->shouldReceive('stateless')->andReturn($socialite);
        $socialite->shouldReceive('fields')->andReturn($socialite);
        $socialite->shouldReceive('user')->andReturn($socialite);
        $socialite->email = 'aaaa@aaab.com';
        $socialite->user = array(
            'name' => ['givenName' => 'nombre', 'familyName' => 'apellido'],
            'id' => 'id',
        );
        \Socialite::shouldReceive('driver')->once()->with('google')->andReturn($socialite);
        $response = $this->get('/auth/google/callback');
        $response->assertStatus(200);
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->google_id,'id');
        $this->assertEquals($response->getOriginalContent()->getData()['linkear'],true);
    }

    public function testConfimarLinkearRedSocial() {
        $persona = Persona::where('mail','aaaa@aaac.com')->first();
        if(!$persona) {
            $persona = new Persona();
            $persona->apellidoPaterno = 'apellido';
            $persona->dni = 'dni';
            $persona->mail = 'aaaa@aaac.com';
            $persona->idLocalidad = 1;
            $persona->fechaNacimiento = new Carbon();
            $persona->nombres = 'nombre';
            $persona->idPais = 1;
            $persona->idPaisResidencia = 1;
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
            $persona->google_id = '';
            $persona->save();
        }
        $response = $this->put('/ajax/usuario/linkear', ['email' => 'aaaa@aaac.com', 'media' => 'google', 'id' => '1234567890']);
        $response->assertStatus(200);
        $this->assertTrue(Auth::check());
        $this->assertEquals(Auth::user()->google_id, '1234567890');
    }

}
