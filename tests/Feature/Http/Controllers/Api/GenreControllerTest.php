<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Genre;
use App\Models\Category;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;
    private $categoryId;
     /**@var \Mockery\Mock $mockGenreController */
     private $mockGenreController;
     /**@var \Mockery\Mock $mokeRequest */
     private $mokeRequest;

    protected function setUp(): void 
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
        $this->categoryId = factory(Category::class)->create()->id;
        $this->mockGenreController = \Mockery::mock(GenreController::class);
        $this->mokeRequest = \Mockery::mock(Request::class);
    }

    public function testIndex()
    {
        
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function testRollbackStore()
    {
        $this->mockGenreController
                    ->makePartial()
                    ->shouldAllowMockingProtectedMethods();

        $this->mockGenreController
                ->shouldReceive('validate')
                ->withAnyArgs()
                ->andReturn(
                    [
                        'name' => 'test'
                    ]
                );
        
        $this->mockGenreController
                ->shouldReceive('rulesStore')
                ->withAnyArgs()
                ->andReturn([]);

        $this->mockGenreController
                ->shouldReceive('handleRelations')
                ->once()
                ->andThrow(new TestException());

        $hasError = false;
        try {
            $this->mockGenreController->store($this->mokeRequest);
        } catch (TestException $exception) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }
        
        $this->assertTrue($hasError);

    }

    public function testRollbackUpdate()
    {
        $this->mockGenreController
                    ->makePartial()
                    ->shouldAllowMockingProtectedMethods();
         
        $this->mockGenreController
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);

        $this->mockGenreController
                ->shouldReceive('validate')
                ->withAnyArgs()
                ->andReturn([
                    'name' => 'test'
                ]);
        
        $this->mockGenreController
                ->shouldReceive('rulesUpdate')
                ->withAnyArgs()
                ->andReturn([]);

        $this->mockGenreController
                ->shouldReceive('handleRelations')
                ->once()
                ->andThrow(new TestException());

        $hasError = false;
        try {
            $this->mockGenreController->store($this->mokeRequest);
        } catch (TestException $exception) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }
        
        $this->assertTrue($hasError);

    }



    public function testShow()
    {
     
        $response = $this->get(route(
            'genres.show', 
        ['genre' => $this->genre->id]
        ));

        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    public function testInvalidationData()
    {

        $data = [
            'name' => '', 
            'categories_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required' );
        $this->assertInvalidationInUpdateAction($data, 'required' );

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction( $data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction( $data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean' );
        $this->assertInvalidationInUpdateAction($data, 'boolean' );

        $data = ['categories_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array' );
        $this->assertInvalidationInUpdateAction($data, 'array' );

        $data = ['categories_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists' );
        $this->assertInvalidationInUpdateAction($data, 'exists' );

        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists' );
        $this->assertInvalidationInUpdateAction($data, 'exists' );

    
    }   


    public function testStore()
    {
        $data = ['name' => 'test']; 
        $response = $this->assertStore(
            $data + ['categories_id' => [$this->categoryId]], 
            $data + ['is_active' => true, 'deleted_at' => null]
        );

        $response->assertJsonStructure(['created_at', 'updated_at']);

        $this->assertHasCategory($response->json('id'), $this->categoryId);

        $data = [
            'name' => 'test', 
            'is_active' => false
        ]; 

        $this->assertStore(
            $data + ['categories_id' => [$this->categoryId]], 
            $data + ['is_active' => false]
        );


    }

    public function testUpdate()
    {
        $data = [
            'name' => 'test',
            'is_active' => true
        ];

        $response = $this->assertUpdate(
            $data + [ 'categories_id' => [$this->categoryId]], 
        $data + ['deleted_at' => null]);

        $this->assertHasCategory($response->json('id'), $this->categoryId);

        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testDestroy()
    {
        $id = $this->genre->id; 

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

    protected function assertHasCategory($genreId, $categoryId)
    {
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $genreId, 
            'category_id' => $categoryId
        ]);
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
