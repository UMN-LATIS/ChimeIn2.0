<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeTest extends TestCase
{

    public function testHomePage()
    {
        $response = $this->get('/');
        $response->assertSuccessful();
        $response->assertViewIs('home');
    }

    public function testCreateDeleteChime()
    {
        $user = factory(\App\User::class)->create();
        $user->permission_number = 100;

        $response = $this->actingAs($user)->get('/api/chime');
        $response->assertJson([
            ]);
        $response = $this->actingAs($user)->post('/api/chime',['chime_name'=>'Test Chime'] );
        $response->assertJson(['name'=>'Test Chime']);
        
        $id = $response->getData()->id;
        $response = $this->actingAs($user)->delete('/api/chime/' . $id);
        $response->assertSuccessful();        
    }
}
