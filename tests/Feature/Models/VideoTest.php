<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Video::class, 1)->create();
        $videos = Video::all();
        $this->assertCount(1, $videos); 
        $videoKey = array_keys($videos->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'title', 
                'description', 
                'year_launched', 
                'opened', 
                'rating', 
                'duration',
                'created_at', 
                'updated_at', 
                'deleted_at'
            ], $videoKey
        );
    }

    public function testCreate()
    {
        $data = [
            'title'=> 'test1', 
            "description"=> "description", 
            "year_launched"=> 2022, 
            "rating"=> "L", 
            "duration"=> 90, 
        ]; 

        $video = Video::create([
            'title'=> 'test1', 
            "description"=> "description", 
            "year_launched"=> 2022, 
            "rating"=> "L", 
            "duration"=> 90, 
        ]);
        $video->refresh();
     
        $this->assertEquals('test1', $video->title);
        $this->assertFalse($video->opened);

        $isValidUuid = Uuid::isValid($video->id);
        $this->assertTrue($isValidUuid);

        $video = Video::create($data + ['opened' => true]);
        $this->assertTrue($video->opened);
    }

    public function testUpdate()
    {
        $video = factory(Video::class)->create()->first();

        $data = [
            'title' => 'test_test_description',  
            'opened' => true
        ];

        $video->update($data);

       foreach($data as $key => $value){
           $this->assertEquals($value, $video->{$key});
       }

    }

    public function testDelete(){
        $video = factory(Video::class)->create()->first();  

        $this->assertNull($video->deleted_at);   

        $video->delete($video->id);  
        $this->assertNotNull($video->deleted_at);
    }
}
