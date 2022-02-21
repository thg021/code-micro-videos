<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\CastMember;
use CastMemberSeeder;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;
class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private $castmember;
    private $serializedFields = [
        'id', 
        'name', 
        'type', 
        'created_at', 
        'updated_at', 
        'deleted_at', 
    ];


    protected function setUp(): void{
        parent::setUp();
        
        $this->castmember = factory(CastMember::class)->create();
    }

    public function testIndex()
    {
        
        $response = $this->get(route('cast_members.index'));

        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ], 
                'links' => [], 
                'meta' => [],
            ]);
    
        $resource = CastMemberResource::collection(collect([$this->castmember]));
        $this->assertResource($response, $resource);

    }

    public function testShow()
    {

        $response = $this->get(route(
            'cast_members.show', 
        ['cast_member' => $this->castmember->id]
        ));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
        
        $id = $response->json('data.id');
        $resource = new CastMemberResource(CastMember::find($id));
        $this->assertResource($response,  $resource);
    }

    public function testStore()
    {
        $data = [
            ['name' => 'test', 'type' => CastMember::TYPE_DIRECTOR], 
            ['name' => 'test', 'type' => CastMember::TYPE_ACTOR], 
        ]; 

        foreach($data as $key => $value){
            $response = $this->assertStore(
                $value, 
                $value + ['deleted_at' => null]
            );

            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);

        }
    }

    public function testUpdate()
    {
        $data = [
            'name' => 'test_update', 
            'type' => CastMember::TYPE_ACTOR
        ];

        $response = $this->assertUpdate(
            $data, 
        $data + ['deleted_at' => null]);

        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);

    }

    public function testDestroy()
    {

        $id = $this->castmember->id; 

        $response = $this->json(
            'DELETE', 
            route(
                'cast_members.destroy', 
                ['cast_member' => $id]
            ));
        
        $response 
            ->assertStatus(204);
        $this->assertNull(CastMember::find($id));
        $this->assertNotNull(CastMember::withTrashed()->find($id));

        $response = $this->json(
            'DELETE', 
            route(
                'cast_members.destroy', 
                ['cast_member' => 'test']
            ));

        $response
            ->assertStatus(404  );
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    protected function routeUpdate()
    {
        return route('cast_members.update', 
        ['cast_member' => $this->castmember->id] );
    }

    protected function model(){
        return CastMember::class;
    }
}
