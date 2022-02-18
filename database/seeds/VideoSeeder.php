<?php

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class VideoSeeder extends Seeder
{
    private $allGenre;
    private $relations = [
        'genres_id' => [],
        'categories_id' => []
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        $dir = \Storage::getDriver()->getAdapter()->getPathPrefix();
        \File::deleteDirectory($dir, true);
        
        $self = $this;
        $this->allGenre = Genre::all();
        Model::reguard(); //mass assignment
        factory(\App\Models\Video::class, 100)
            ->make()
            ->each(function (Video $video) use ($self) {
                $self->fetchRelations();
                \App\Models\Video::create(
                    array_merge(
                        $video->toArray(), //thumb_file, banner_file
                        [
                            'thumb_file' => $self->getImageFile(),
                            'banner_file' => $self->getImageFile(),
                            'trailer_file' => $self->getVideoFile(),
                            'video_file' => $self->getVideoFile(),
                        ],
                        $this->relations
                    )
                );
            });
        Model::unguard();
    }

    public function fetchRelations()
    {
        $subGenre = $this->allGenre->random(5)->load('categories');
        $categoriesId = [];
        foreach ($subGenre as $genre) {
            array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);
        $genreId = $subGenre->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesId;
        $this->relations['genres_id'] = $genreId;
    }

    public function getImageFile()
    {
        return new UploadedFile(
            storage_path('faker/thumbs/thumb_test.jpg'),
            'Laravel Framework.png'
        );
    }

    public function getVideoFile()
    {
        return new UploadedFile(
            storage_path('faker/videos/videos_test.mp4'),
            'videos_test.mp4'
        );
    }
}
