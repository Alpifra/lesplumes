<?php

namespace Tests\Feature\Models;

use App\Models\Media;
use App\Models\Story;
use Database\Factories\MediaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MediaTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * @group model
     * @group media
     */
    public function create_model()
    {
        $media = new Media;

        $target_disk = Config::get('mediable.default_disk');
        $allowed_disks = Config::get('mediable.allowed_disks');

        $file = MediaFactory::createFile();
        $extension = $file->getExtension();
        $name = str_replace(".{$extension}", '', $file->getFilename());
        $story = Story::factory()->createOne();

        $mime_type = 'appilication/pdf';
        $size = $file->getSize();

        $media->disk = $target_disk;
        $media->directory = Media::STORY_MEDIA_DIRECTORY;
        $media->filename = $name;
        $media->extension = $extension;
        $media->mime_type = $mime_type;
        $media->aggregate_type = $extension;
        $media->size = $size;
        $media->story()->associate($story);

        self::assertNull($media->id);

        $media->save();

        self::assertNotNull($media->id);
        self::assertFileExists($media->getAbsolutePath());
        self::assertEquals($target_disk, $media->disk);
        self::assertEquals(Media::STORY_MEDIA_DIRECTORY, $media->directory);
        self::assertEquals($name, $media->filename);
        self::assertEquals($extension, $media->extension);
        self::assertEquals($mime_type, $media->mime_type);
        self::assertEquals($extension, $media->aggregate_type);
        self::assertEquals($size, $media->size);
        self::assertNotNull($media->created_at);
        self::assertNotNull($media->updated_at);
        self::assertContains($media->disk, $allowed_disks);
        self::assertEquals($story->id, $media->story->id);
    }

    /**
     * @test
     * @group model
     * @group media
     */
    public function edit_model()
    {
        $media = Media::factory()->createOne();

        $target_disk = Config::get('mediable.default_disk');
        $allowed_disks = Config::get('mediable.allowed_disks');

        $file = MediaFactory::createFile();
        $extension = $file->getExtension();
        $name = str_replace(".{$extension}", '', $file->getFilename());

        $mime_type = 'appilication/pdf';
        $size = $file->getSize();

        $media->disk = $target_disk;
        $media->directory = Media::STORY_MEDIA_DIRECTORY;
        $media->filename = $name;
        $media->extension = $extension;
        $media->mime_type = $mime_type;
        $media->aggregate_type = $extension;
        $media->size = $size;

        $media->save();

        self::assertNotNull($media->id);
        self::assertFileExists($media->getAbsolutePath());
        self::assertEquals($target_disk, $media->disk);
        self::assertEquals(Media::STORY_MEDIA_DIRECTORY, $media->directory);
        self::assertEquals($name, $media->filename);
        self::assertEquals($extension, $media->extension);
        self::assertEquals($mime_type, $media->mime_type);
        self::assertEquals($extension, $media->aggregate_type);
        self::assertEquals($size, $media->size);
        self::assertNotNull($media->created_at);
        self::assertNotNull($media->updated_at);
        self::assertContains($media->disk, $allowed_disks);
    }

    /**
     * @test
     * @group model
     * @group media
     */
    public function delete_model()
    {
        $media = Media::factory()
            ->for(Story::factory()->create())
            ->createOne();

        self::assertFileExists($media->getAbsolutePath());

        $media->delete();

        self::assertEmpty(Media::all());
        self::assertNotEmpty(Story::all());
        self::assertFileDoesNotExist($media->getAbsolutePath());
    }
}
