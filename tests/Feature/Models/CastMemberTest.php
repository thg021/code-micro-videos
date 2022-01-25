<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

class CastMemberTest extends TestCase
{
  use DatabaseMigrations;

  public function testList()
  {
    factory(CastMember::class, 1)->create(); 
    $castmembers = CastMember::all();
    $this->assertCount(1, $castmembers);
    $castmembersKeys = array_keys($castmembers->first()->getAttributes());
    $this->assertEqualsCanonicalizing([
        'name', 
        'type', 
        'id', 
        'created_at', 
        'deleted_at', 
        'updated_at'
    ], $castmembersKeys);
  }

  public function testCreate()
  {
        $castmember = CastMember::create([
            'name' => 'test',
            'type' => 1
        ]);
        $castmember->refresh();
        
        $this->assertEquals('test', $castmember->name);
        $this->assertEquals('1', $castmember->type);

        $isValidUuid = Uuid::isValid($castmember->id);
        $this->assertTrue($isValidUuid);
  }

  public function testUpdate()
  {
        $castmember = factory(CastMember::class)->create([
            'name'=>'test', 
            'type' => 1
        ])->first();

        $data = [
            'name' => 'test_updated', 
            'type' => 2
        ];

        $castmember->update($data);

        foreach($data as $key => $value){
            $this->assertEquals($value, $castmember->{$key});
        }
  }

  public function testDelete()
  {
        $castmember = factory(CastMember::class)->create()->first();  

        $this->assertNull($castmember->deleted_at);   

        $castmember->delete($castmember->id);  
        $this->assertNotNull($castmember->deleted_at);
  }
}
