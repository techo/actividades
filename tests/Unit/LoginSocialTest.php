<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Socialite;
use Mockery;

class LoginSocialTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function facebook_devuelve_datos()
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
        
        $response = $this->get('/auth/facebook/callback')->assertStatus(200);

        $this->assertEquals($response->getOriginalContent()->getData()['persona']->nombre,'nombre');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->apellido,'apellido');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->email,'email');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->facebook_id,'id');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->google_id,'');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->sexo,'M');
    }

    /** @test */
    public function google_devuelve_datos()
    {
    	$socialite = \Mockery::mock('Laravel\Socialite\Two\User');
    	$socialite->shouldReceive('stateless')->andReturn($socialite);
    	$socialite->shouldReceive('fields')->andReturn($socialite);
    	$socialite->shouldReceive('user')->andReturn($socialite);
    	$socialite->email = 'email';
    	$socialite->user = array(
            'given_name' => 'nombre', 
            'family_name' => 'apellido',
            'id' => 'id',
        );
    	\Socialite::shouldReceive('driver')->once()->with('google')->andReturn($socialite);
        
        $response = $this->get('/auth/google/callback')->assertStatus(200);

        $this->assertEquals($response->getOriginalContent()->getData()['persona']->nombre,'nombre');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->apellido,'apellido');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->email,'email');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->facebook_id,'');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->google_id,'id');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->sexo,'');
    }

    /** @test */
    public function facebook_devuelve_mensaje_de_error_para_usuario_sin_mail()
    {
        $socialite = \Mockery::mock('Laravel\Socialite\Two\User');
        $socialite->shouldReceive('stateless')->andReturn($socialite);
        $socialite->shouldReceive('fields')->andReturn($socialite);
        $socialite->shouldReceive('user')->andReturn($socialite);
        $socialite->user = array(
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'id' => 'id',
            'gender' => 'male',
        );
        \Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($socialite);
        
        $response = $this->get('/auth/facebook/callback')->assertStatus(200);

        $this->assertEquals(
            $response->getOriginalContent()->getData()['mensaje'], 
            "La cuenta de facebook no tiene un email vinculado. Intente con otra red social o con usuario y contraseña"
        );
    }
}
