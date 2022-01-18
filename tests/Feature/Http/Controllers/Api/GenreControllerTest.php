<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use App\Models\Genre;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);

    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route(
            'genres.show', 
        ['genre' => $genre->id]
        ));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testInvalidationData()
    {
        $response = $this->json(
            'POST', 
            route('genres.store'), 
            []);

        $this->assertInvalidationRequired($response);
        
        $response = $this->json(
            'POST', 
            route('genres.store'), 
            [
                'name' => str_repeat('a', 256), 
                'is_active'=> 'a'
            ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $genre = factory(Genre::class)->create();
        $response = $this->json('PUT', route(
            'genres.update', 
            ['genre' => $genre->id]), 
            []
        );

        $this->assertInvalidationRequired($response);
             
        $response = $this->json('PUT', route(
            'genres.update', 
            ['genre' => $genre->id]), 
            [
                'name' => str_repeat('a', 256), 
                'is_active' => 'a'
            ]
        );

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }   

    protected function assertInvalidationRequired(TestResponse $response) {
        $this->assertInvalidationFields(
            $response, 
            ['name'], 
            'required'
        );
        $response
        ->assertJsonMissingValidationErrors(['is_active']);
    }

    protected function assertInvalidationMax(TestResponse $response) {
        $this->assertInvalidationFields(
            $response, 
            ['name'], 
            'max.string', 
            ['max' => 255]
        );

        $response
        ->assertJsonValidationErrors(['name']);
    }

    protected function assertInvalidationBoolean(TestResponse $response) {
        $this->assertInvalidationFields(
            $response, 
            ['is_active'], 
            'boolean'
        );
        $response
            ->assertJsonValidationErrors(['is_active']);
    }

    public function testStore()
    {
        $response = $this->json(
            'POST', 
            route('genres.store'), 
            [
                'name' => 'test'
            ]);
        $id = $response->json('id');
        $genre = Genre::find($id);
        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));

        $response = $this->json(
            'POST', 
            route('genres.store'), 
            [
                'name' => 'test', 
                'is_active' => false
            ]);
      
        $response
            ->assertJsonFragment([
                'is_active' => false
            ]);
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'name' => 'genre test', 
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT', 
            route('genres.update', [ 'genre' => $genre->id]), 
            [
                'name' => 'test',
                'is_active' => true
            ]);
        $id = $response->json('id');
        $genre = Genre::find($id);
        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'test', 
                'is_active' => true
            ]);

  
    }

    public function testDestroy()
    {
        $genre = factory(Genre::class)->create();
        $id = $genre->id; 

        $response = $this->json(
            'DELETE', 
            route(
                'genres.destroy', 
                ['genre' => $id]
            ));
        
        $response 
            ->assertStatus(204);
        $this->assertNull(Genre::find($id));
        $this->assertNotNull(Genre::withTrashed()->find($id));

        $response = $this->json(
            'DELETE', 
            route(
                'genres.destroy', 
                ['genre' => 'test']
            ));

        $response
            ->assertStatus(404  );
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id] );
    }

    protected function model(){
        return Genre::class;
    }

}
