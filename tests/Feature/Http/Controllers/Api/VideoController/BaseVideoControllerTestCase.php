<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
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

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);
        $this->sendData = [
            'title' => 'title', 
            'description' => 'description', 
            'year_launched' => 2022, 
            'rating' => Video::RATING_LIST[0],
            'duration' => 90, 
            'categories_id' => [$category->id], 
            'genres_id' => [$genre->id]
        ];

        $this->mockVideoController = \Mockery::mock(VideoController::class);
        $this->mokeRequest = \Mockery::mock(Request::class);
    }
}