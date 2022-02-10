<?php

namespace Tests\Unit\Models;

use App\Models\Video;
use Tests\TestCase;

class VideoUnitTest extends TestCase
{
  
  
    private $video;

    protected function setUp(): void 
    {
        parent::setUp();
        $this->video = new Video();
    }

    public function testFillableAttribute()
    {
        $fillable = [
            'title', 
            'description', 
            'year_launched', 
            'opened', 
            'rating', 
            'duration'
        ];
        $this->assertEquals($fillable, $this->video->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [
            \Illuminate\Database\Eloquent\SoftDeletes::class, 
            \App\Models\Traits\Uuid::class, 
            \App\Models\Traits\UploadFiles::class
        ];

        $videoTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $videoTraits);
        
    }

    public function testKeyTypes()
    {
        $keyTypes = 'string';
        $this->assertEquals($keyTypes, $this->video->getKeyType());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->video->incrementing);
    }

    public function testCastsAttributes()
    {
        $casts = [ 
            'opened' => 'boolean',
            'year_launched' => 'integer', 
            'duration' => 'integer'
        ]; 
        $this->assertEquals($casts, $this->video->getCasts());
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach($dates as $date){
            $this->assertContains($date, $this->video->getDates());
        }

        $this->assertCount(count($dates), $this->video->getDates());
    }
}


