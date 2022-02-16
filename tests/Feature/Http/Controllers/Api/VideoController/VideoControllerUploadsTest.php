<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Tests\Traits\TestValidations;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Http\Controllers\Api\VideoController\BaseVideoControllerTestCase;
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


    public function testInvalidationFilesField()
    {
        $dataFilesField = [
            ['video_file', 'mp4', Video::VIDEO_FILE_MAX_SIZE, ['values' => 'video/mp4']], 
            ['thumb_file', 'jpeg', Video::THUMB_FILE_MAX_SIZE, ['values' => 'image/jpeg']], 
            ['trailer_file', 'mp4', Video::TRAILER_FILE_MAX_SIZE, ['values' => 'video/mp4']], 
            ['banner_file', 'jpeg', Video::BANNER_FILE_MAX_SIZE, ['values' => 'image/jpeg']], 
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

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json(
            'POST', 
            $this->routeStore(), 
            $this->sendData + 
            [
                'categories_id' => [$category->id], 
                'genres_id' => [$genre->id]
            ] +
            $files
            );

        $response->assertStatus(201);
        $id = $response->json('id');
        foreach($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json(
            'PUT', 
            $this->routeUpdate(), 
            $this->sendData + 
            [
                'categories_id' => [$category->id], 
                'genres_id' => [$genre->id]
            ] +
            $files
            );

        $response->assertStatus(200);
        $id = $response->json('id');
        foreach($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
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
