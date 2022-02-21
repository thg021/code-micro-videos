<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Traits\TestValidations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Tests\Feature\Http\Controllers\Api\VideoController\BaseVideoControllerTestCase;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\Traits\TestUploads;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
   
    use TestValidations, TestUploads;

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


    public function testInvalidationVideoField()
    {
        $dataFilesField = [
            ['video_file', 'mp4', Video::VIDEO_FILE_MAX_SIZE, ['values' => 'video/mp4']], 
            ['trailer_file', 'mp4', Video::TRAILER_FILE_MAX_SIZE, ['values' => 'video/mp4']],  
        ];

        foreach($dataFilesField as $fileField){
            $this->assertInvalidationFile(
                $fileField[0], 
                $fileField[1], 
                $fileField[2], 
                'mimetypes', $fileField[3]
            );
        }

    }

    public function testInvalidationImageField()
    {
        $dataFilesField = [
            ['thumb_file', 'jpeg', Video::THUMB_FILE_MAX_SIZE, ['values' => 'image/jpeg']], 
            ['banner_file', 'jpeg', Video::BANNER_FILE_MAX_SIZE, ['values' => 'image/jpeg']], 
        ];

        foreach($dataFilesField as $fileField){
            $this->assertInvalidationFile(
                $fileField[0], 
                $fileField[1], 
                $fileField[2], 
                'image',
            );
        }

    }

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'POST', 
            $this->routeStore(), 
            $this->sendData +
            $files
            );

        $response->assertStatus(201);
        $this->assertFilesOnPersist($response, $files);
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();


        $response = $this->json(
            'PUT', 
            $this->routeUpdate(), 
            $this->sendData + $files
            );

        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);
        
        $newFiles = [
            'thumb_file' => UploadedFile::fake()->create('thumb_file.jpg'),
            'video_file' => UploadedFile::fake()->create('video.mp4')
        ];

        $response = $this->json(
            'PUT', 
        $this->routeUpdate(), 
            $this->sendData + $newFiles
        );

        $response->assertStatus(200);
        $this->assertFilesOnPersist(
            $response, 
            Arr::except($files, ['thumb_file', 'video_file']) + $newFiles
        );

        $id = $response->json('data.id'); 
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
    }

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $response->json('id') ?? $response->json('data.id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'), 
            'thumb_file' => UploadedFile::fake()->create('thumb_file.jpeg'),
            'banner_file' => UploadedFile::fake()->create('banner_file.jpeg'),
            'trailer_file' => UploadedFile::fake()->create('trailer_file.mp4'),
        ];
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


}
