<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Video;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
   
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;

    protected function setUp(): void
    {
        parent::setUp(); 
        $this->video = factory(Video::class)->create();
    }

    public function testIndex()
    {
      
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);

    }

    public function testShow()
    {

        $response = $this->get(route(
            'videos.show', 
        ['video' => $this->video->id]
        ));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationData()
    {
        $data = [
            'title' => '', 
            'description' => '', 
            'year_launched' => '', 
            'rating' => '', 
            'duration' => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required' );
        $this->assertInvalidationInUpdateAction($data, 'required' );
    }   

    public function testInvalidationMax()
    {
        $data = ['title' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction( $data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction( $data, 'max.string', ['max' => 255]);

    }

    public function testInvalidationBoolean()
    {
        $data = ['opened' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean' );
        $this->assertInvalidationInUpdateAction($data, 'boolean' );
    }

    public function testInvalidationInteger()
    {
        $data = ['duration' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'integer' );
        $this->assertInvalidationInUpdateAction($data, 'integer' );
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = ['year_launched' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y'] );
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y'] );

    }

    public function testInvalidationRatingField()
    {
        $data = ['rating' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');

    }

    public function testStore()
    {
        $data = ['name' => 'test']; 
        $response = $this->assertStore(
            $data, 
            $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]
        );

        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name' => 'test', 
            'description' => 'description test', 
            'is_active' => false
        ]; 

        $this->assertStore(
            $data, 
            $data + ['description' => 'description test', 'is_active' => false]
        );
    }

    public function testUpdate()
    {

        $data = [
            'name' => 'test',
            'description' => 'test', 
            'is_active' => true
        ];

        $response = $this->assertUpdate(
            $data, 
        $data + ['deleted_at' => null]);

        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name' => 'test',
            'description' => ''
        ];

        
        $this->assertUpdate(
            $data, 
        array_merge($data , ['description' => null]));
       
        $data['description'] = 'test';
        $this->assertUpdate(
            $data, 
        array_merge($data , ['description' => 'test']));

        $data['description'] = null;
        $this->assertUpdate(
            $data, 
        array_merge($data , ['description' => null]));

    }

    public function testDestroy()
    {

        $id = $this->video->id; 

        $response = $this->json(
            'DELETE', 
            route(
                'videos.destroy', 
                ['video' => $id]
            ));
        
        $response 
            ->assertStatus(204);
        $this->assertNull(Video::find($id));
        $this->assertNotNull(Video::withTrashed()->find($id));

        $response = $this->json(
            'DELETE', 
            route(
                'videos.destroy', 
                ['video' => 'test']
            ));

        $response
            ->assertStatus(404  );
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id] );
    }

    protected function model(){
        return Video::class;
    }
}
