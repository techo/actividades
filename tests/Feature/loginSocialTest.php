<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Socialite;
use Mockery;	


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
    		'gender' => 'male',
    	);
    	\Socialite::shouldReceive('driver')->once()->with('google')->andReturn($socialite);
        $response = $this->get('/auth/google/callback');
        $response->assertStatus(200);
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->nombre,'nombre');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->apellido,'apellido');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->email,'email');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->facebook_id,'');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->google_id,'id');
        $this->assertEquals($response->getOriginalContent()->getData()['persona']->sexo,'M');
    }


}
