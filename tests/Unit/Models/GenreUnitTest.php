<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use Tests\TestCase;

class GenreUnitTest extends TestCase
{
  
  
    private $genre;

    protected function setUp(): void 
    {
        parent::setUp();
        $this->genre = new Genre();
    }

    public function testFillableAttribute()
    {
        $fillable = ['name', 'is_active'] ;
        $this->assertEquals($fillable, $this->genre->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [
            \Illuminate\Database\Eloquent\SoftDeletes::class, 
            \App\Models\Traits\Uuid::class
        ];

        $genreTraits = array_keys(class_uses(genre::class));
        $this->assertEquals($traits, $genreTraits);
        
    }

    public function testKeyTypes()
    {
        $keyTypes = 'string';
        $this->assertEquals($keyTypes, $this->genre->getKeyType());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->genre->incrementing);
    }

    public function testCastsAttributes()
    {
        $casts = [ 'is_active' => 'boolean']; 
        $this->assertEquals($casts, $this->genre->getCasts());
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach($dates as $date){
            $this->assertContains($date, $this->genre->getDates());
        }

        $this->assertCount(count($dates), $this->genre->getDates());
    }
}


