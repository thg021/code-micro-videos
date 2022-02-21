<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;


class BasicCrudControllerTest extends TestCase
{
    private $controller; 
    /**@var \Mockery\Mock $requestMock */
    private $requestMock;
    protected function setUp(): void {
        parent::setUp(); 
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();
        $this->requestMock = \Mockery::mock(Request::class);
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        /**@var CategoryStub $category */
        $category = CategoryStub::create([
            'name' => 'test', 
            'description' => 'test'
        ]);
       
        $resource = $this->controller->index(); 
        $serialized = $resource->response()->getData(true);
        $this->assertEquals(
            [$category->toArray()], 
            $serialized['data']
        );

        $this->assertArrayHasKey('meta', $serialized);
        $this->assertArrayHasKey('links', $serialized);
    }


    public function testInvalidatonDataInStore()
    {
         
        $this->expectException(ValidationException::class);

        $this->requestMock->shouldReceive('all')
                ->once()
                ->andReturn(['name'=>'']);
        $this->controller->store($this->requestMock);
    }

    public function testStore()
    {
        $this->requestMock->shouldReceive('all')
            ->once()
            ->andReturn([
                'name' => 'name_test', 
                'description' => 'description_test'
            ]);
        $resource = $this->controller->store($this->requestMock);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(), 
            $serialized['data']
        );
    }

    public function testIfFindOrFailFetchModel()
    {
        $category = CategoryStub::create([
            'name' => 'test', 
            'description' => 'test'
        ]);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $resource = $reflectionMethod->invokeArgs($this->controller, [$category->id]);
        $this->assertInstanceOf(CategoryStub::class, $resource);
    }

    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);
        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $resource = $reflectionMethod->invokeArgs($this->controller, [0]);
        $this->assertInstanceOf(CategoryStub::class, $resource);
    }

    public function testShow()
    {
        $category = CategoryStub::create([
            'name' => 'test', 
            'description' => 'test'
        ]);

        $resource = $this->controller->show($category->id);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals(
            $category->toArray(), 
            $serialized['data']
        );
    }

    public function testUpdate()
    {
        $category = CategoryStub::create([
            'name' => 'test', 
            'description' => 'test'
        ]);

        $this->requestMock->shouldReceive('all')
            ->once()
            ->andReturn([
                'name' => 'name_updated', 
                'description' => 'description_updated'
            ]);
        
        $resource = $this->controller->update($this->requestMock, $category->id);
        $serialized = $resource->response()->getData(true);
        $category->refresh();
        $this->assertEquals(
            $category->toArray(), 
            $serialized['data']
        );
    }

    public function testDestroy()
    {
        $category = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
        $response = $this->controller->destroy($category->id);
        $this
            ->createTestResponse($response)
            ->assertStatus(204);
        $this->assertCount(0, CategoryStub::all());
    }

}
