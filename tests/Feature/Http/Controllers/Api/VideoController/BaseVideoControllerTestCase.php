<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\TestCase;

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;


    protected $sendData;
    /**@var \Mockery\Mock $mockVideoController */
    protected $mockVideoController;
    /**@var \Mockery\Mock $mokeRequest */
    protected $mokeRequest;

    protected function setUp(): void
    {
        parent::setUp(); 
        $this->video = factory(Video::class)->create([
            'opened' => false
        ]);
        $this->sendData = [
            'title' => 'title', 
            'description' => 'description', 
            'year_launched' => 2022, 
            'rating' => Video::RATING_LIST[0],
            'duration' => 90    
        ];

        $this->mockVideoController = \Mockery::mock(VideoController::class);
        $this->mokeRequest = \Mockery::mock(Request::class);
    }
}