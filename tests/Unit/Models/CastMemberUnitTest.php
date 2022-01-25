<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use Tests\TestCase;

class CastMemberUnitTest extends TestCase
{
  
  
    private $castmember;

    protected function setUp(): void 
    {
        parent::setUp();
        $this->castmember = new CastMember();
    }

    public function testFillableAttribute()
    {
        $fillable = ['name', 'type'] ;
        $this->assertEquals($fillable, $this->castmember->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [
            \Illuminate\Database\Eloquent\SoftDeletes::class, 
            \App\Models\Traits\Uuid::class
        ];

        $castmemberTraits = array_keys(class_uses(castmember::class));
        $this->assertEquals($traits, $castmemberTraits);
        
    }

    public function testKeyTypes()
    {
        $keyTypes = 'string';
        $this->assertEquals($keyTypes, $this->castmember->getKeyType());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->castmember->incrementing);
    }

    public function testCastsAttributes()
    {
        $casts = [ 'type' => 'integer']; 
        $this->assertEquals($casts, $this->castmember->getCasts());
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach($dates as $date){
            $this->assertContains($date, $this->castmember->getDates());
        }

        $this->assertCount(count($dates), $this->castmember->getDates());
    }
}


