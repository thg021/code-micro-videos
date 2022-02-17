<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Support\Arr;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
   
    use TestValidations, TestSaves;


    public function testIndex()
    {
      
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);

    }

    // public function testRollbackStore()
    // {
    //     $this->mockVideoController
    //                 ->makePartial()
    //                 ->shouldAllowMockingProtectedMethods();

    //     $this->mockVideoController
    //             ->shouldReceive('validate')
    //             ->withAnyArgs()
    //             ->andReturn($this->sendData);

                
        
    //     $this->mockVideoController
    //             ->shouldReceive('rulesStore')
    //             ->withAnyArgs()
    //             ->andReturn([]);
    //     $this->mockVideoController
    //             ->shouldReceive('handleRelations')
    //             ->once()
    //             ->andThrow(new TestException());

    //     $this->mokeRequest
    //         ->shouldReceive('get')
    //         ->withAnyArgs()
    //         ->andReturn([]);

    //     $hasError = false;
    //     try {
    //         $this->mockVideoController->store($this->mokeRequest);
    //     } catch (TestException $exception) {
    //         $this->assertCount(1, Video::all());
    //         $hasError = true;
    //     }
        
    //     $this->assertTrue($hasError);

    // }

    // public function testRollbackUpdate()
    // {
    //     $this->mockVideoController
    //                 ->makePartial()
    //                 ->shouldAllowMockingProtectedMethods();
         
    //     $this->mockVideoController
    //         ->shouldReceive('findOrFail')
    //         ->withAnyArgs()
    //         ->andReturn($this->video);

    //     $this->mockVideoController
    //             ->shouldReceive('validate')
    //             ->withAnyArgs()
    //             ->andReturn([
    //                 'name' => 'test'
    //             ]);
        
    //     $this->mockVideoController
    //             ->shouldReceive('rulesUpdate')
    //             ->withAnyArgs()
    //             ->andReturn([]);

    //     $this->mockVideoController
    //             ->shouldReceive('handleRelations')
    //             ->once()
    //             ->andThrow(new TestException());

    //         $this->mokeRequest
    //             ->shouldReceive('get')
    //             ->withAnyArgs()
    //             ->andReturn([]);
    

    //     $hasError = false;
    //     try {
    //         $this->mockVideoController->update($this->mokeRequest, 1);
    //     } catch (TestException $exception) {
    //         $this->assertCount(1, Video::all());
    //         $hasError = true;
    //     }
        
    //     $this->assertTrue($hasError);

    // }

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

    // public function testInvalidationVideoField()
    // {
    //     \Storage::fake();
    //     $file = UploadedFile::fake()->create('video.mp4', 5000);
    //     $data = ['video_file' => $file ];
    //     $this->assertInvalidationInStoreAction( $data, 'max.file', ['max' => Video::VIDEO_FILE_MAX_SIZE]);
    //     $this->assertInvalidationInUpdateAction( $data, 'max.file', ['max' => Video::VIDEO_FILE_MAX_SIZE]);

    // }

    // public function testInvalidationMimeTypeVideo()
    // {
    //     \Storage::fake();
    //     $file = UploadedFile::fake()->create('video', );
    //     $data = ['video_file' => $file ];
    //     $this->assertInvalidationInStoreAction( $data, 'mimetypes', ['values' => 'video/mp4']);
    //     $this->assertInvalidationInUpdateAction( $data, 'mimetypes', ['values' => 'video/mp4']);

    // }


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


    public function testSaveWithoutFiles()
    {
        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);

        $data = [ 
                    [
                        'send_data' => $this->sendData, 
                        'test_data' => $testData + ['opened' => false]
                    ],
                    [
                        'send_data' => $this->sendData + [
                            'opened' => true], 
                        'test_data' => $testData + ['opened' => true]
                    ],
                    [
                        'send_data' => $this->sendData + [
                            'rating' => Video::RATING_LIST[3]], 
                        'test_data' => $testData + ['rating' => Video::RATING_LIST[3]]
                    ],
                ];

                foreach($data as $key => $value){
                    $response = $this->assertStore(
                        $value['send_data'], 
                        $value['test_data'] + ['deleted_at' => null]
                    );

                    $response->assertJsonStructure([
                        'created_at', 
                        'updated_at'
                    ]);

                    $this->assertHasCategory(
                        $response->json('id'), 
                        $value['send_data']['categories_id'][0]
                    );

                    $this->assertHasgenre(
                        $response->json('id'), 
                        $value['send_data']['genres_id'][0]
                    );

                    $response = $this->assertUpdate(
                        $value['send_data'], 
                        $value['test_data'] + ['deleted_at' => null]
                    );

                    $response->assertJsonStructure([
                        'created_at', 
                        'updated_at'
                    ]);

                    $this->assertHasCategory(
                        $response->json('id'), 
                        $value['send_data']['categories_id'][0]
                    );

                    $this->assertHasgenre(
                        $response->json('id'), 
                        $value['send_data']['genres_id'][0]
                    );


                }
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
