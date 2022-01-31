<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Video;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;
use Illuminate\Http\Request;
use PhpParser\ErrorHandler\Collecting;
use Tests\Exceptions\TestException;

class VideoControllerTest extends TestCase
{
   
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;
    private $sendData;
    /**@var \Mockery\Mock $mockVideoController */
    private $mockVideoController;
    /**@var \Mockery\Mock $mokeRequest */
    private $mokeRequest;

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

    public function testIndex()
    {
      
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);

    }

    public function testRollbackStore()
    {
        $this->mockVideoController
                    ->makePartial()
                    ->shouldAllowMockingProtectedMethods();

        $this->mockVideoController
                ->shouldReceive('validate')
                ->withAnyArgs()
                ->andReturn($this->sendData);
        
        $this->mockVideoController
                ->shouldReceive('rulesStore')
                ->withAnyArgs()
                ->andReturn([]);
        $this->mockVideoController
                ->shouldReceive('handleRelations')
                ->once()
                ->andThrow(new TestException());

        try {
            
            $this->mockVideoController->store($this->mokeRequest);
        } catch (TestException $exception) {
            $this->assertCount(1, Video::all());
        }
        

    }

    public function testRollbackUpdate()
    {
        $this->mockVideoController
                    ->makePartial()
                    ->shouldAllowMockingProtectedMethods();
         
        $this->mockVideoController
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->video);

        $this->mockVideoController
                ->shouldReceive('validate')
                ->withAnyArgs()
                ->andReturn([
                    'name' => 'test'
                ]);
        
        $this->mockVideoController
                ->shouldReceive('rulesUpdate')
                ->withAnyArgs()
                ->andReturn([]);

        $this->mockVideoController
                ->shouldReceive('handleRelations')
                ->once()
                ->andThrow(new TestException());

        $hasError = false;
        try {
            $this->mockVideoController->update($this->mokeRequest, 1);
        } catch (TestException $exception) {
            $this->assertCount(1, Video::all());
            $hasError = true;
        }
        
        $this->assertTrue($hasError);

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

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '', 
            'description' => '', 
            'year_launched' => '', 
            'rating' => '', 
            'duration' => '',
            'categories_id' => '', 
            'genres_id' => ''
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

    public function testInvalidationCategoriesIdField()
    {
        $data = [
            'categories_id' => 'a'
        ];

        $this->InvalidationFields($data, 'array');

        $data = [
            'categories_id' => [10]
        ];

        $this->InvalidationFields($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();

        $data = [
            'categories_id' => [$category->id]
        ];

        $this->InvalidationFields($data, 'exists');


    }

    public function testInvalidationGenresIdField()
    {
        $data = [
            'genres_id' => 'a'
        ];

        $this->InvalidationFields($data, 'array');

        $data = [
            'genres_id' => [10]
        ];

        $this->InvalidationFields($data, 'exists');
        $genre = factory(Genre::class)->create();
        $genre->delete();

        $data = [
            'genres_id' => [$genre->id]
        ];

        $this->InvalidationFields($data, 'exists');
    }

    public function testSave()
    {

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();

        $data = [
            [
                'send_data' => $this->sendData + [
                    'categories_id' => [$category->id], 
                    'genres_id' => [$genre->id]
                ], 
                'test_data' => $this->sendData + ['opened' => false]
            ], 
            [
                'send_data' => $this->sendData + [
                    'opened' => true, 
                    'categories_id' => [$category->id], 
                    'genres_id' => [$genre->id]
                ], 
                'test_data' => $this->sendData + ['opened' => true]
            ], 
            [
                'send_data' => $this->sendData + [
                    'rating' => Video::RATING_LIST[2], 
                    'categories_id' => [$category->id], 
                    'genres_id' => [$genre->id]
                ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[2]]
            ]
        ];

        foreach($data as $key => $value)
        {
         
            $response = $this->assertStore(
                $value['send_data'], 
                $value['test_data'] + ['deleted_at' => null]);
    
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);

            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]); 
            $this->assertHasGenre($response->json('id'), $value['send_data']['genres_id'][0]); 

            $response = $this->assertUpdate(
                $value['send_data'], 
                $value['test_data'] + ['deleted_at' => null]);
    
            $response->assertJsonStructure([
                'created_at',
                'updated_at', 
            ]);

        }
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
            ->assertStatus(404 );
    }


    protected function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId, 
            'category_id' => $categoryId
        ]);
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoId, 
            'genre_id' => $genreId
        ]);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($categoriesId);
        $genreId = $genre->id;
      

        $response = $this->json('POST', $this->routeStore(), $this->sendData + [
            'genres_id' => [$genreId], 
            'categories_id' => [$categoriesId[0]]
        ]); 

        
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0], 
            'video_id' => $response->json('id')
        ]);

      
        $response = $this->json(
            'PUT', 
            route('videos.update', [
                'video' => $response->json('id')
            ]), 
            $this->sendData + [
                'genres_id' => [$genreId], 
                'categories_id' => [$categoriesId[1], $categoriesId[2]]
            ]
        );

        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0], 
            'video_id' => $response->json('id')
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1], 
            'video_id' => $response->json('id')
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2], 
            'video_id' => $response->json('id')
        ]);
    }

    public function testSyncGenres()
    {
        /** @var Collection $genres */
        $genres = factory(Genre::class, 3)->create();
        $genresId = $genres->pluck('id')->toArray();
        $categoryId = factory(Category::class)->create()->id;
        $genres->each(function($genre) use($categoryId) {
            $genre->categories()->sync($categoryId);
        });


        $response = $this->json('POST', $this->routeStore(), $this->sendData + [
            'genres_id' => [$genresId[0]], 
            'categories_id' => [$categoryId]
        ]); 

        
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[0], 
            'video_id' => $response->json('id')
        ]);

      
        $response = $this->json(
            'PUT', 
            route('videos.update', [
                'video' => $response->json('id')
            ]), 
            $this->sendData + [
                'genres_id' => [$genresId[1], $genresId[2]], 
                'categories_id' => [$categoryId]
            ]
        );

        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0], 
            'video_id' => $response->json('id')
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[1], 
            'video_id' => $response->json('id')
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[2], 
            'video_id' => $response->json('id')
        ]);
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

    protected function InvalidationFields(array $data, string $rule){
        $this->assertInvalidationInStoreAction($data, $rule);
        $this->assertInvalidationInUpdateAction($data, $rule);
    }
}
