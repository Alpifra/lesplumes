<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Plank\Mediable\Media as MediableMedia;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $file = self::createFile();
        $extension = $file->getExtension();
        $name = str_replace(".{$extension}", '', $file->getFilename());

        return [
            'disk'           => Config::get('mediable.default_disk'),
            'directory'      => Media::STORY_MEDIA_DIRECTORY,
            'filename'       => $name,
            'extension'      => $extension,
            'mime_type'      => 'application/pdf',
            'aggregate_type' => $extension,
            'size'           => $file->getSize(),
            'story_id'       => Story::factory()
        ];
    }

    public static function createFile(): UploadedFile
    {
        $fake_pdf = new UploadedFile(storage_path('framework/testing/fake.pdf'), 'fake.pdf');
        $target_disk = Config::get('mediable.default_disk');
        $target_disk_path = Media::STORY_MEDIA_DIRECTORY . '/';
        $filename = uniqid() . '.' . MediableMedia::TYPE_PDF;
        $disk_filepath = $target_disk_path . $filename;
        Storage::disk($target_disk)->put($disk_filepath, $fake_pdf->getContent());

        return new UploadedFile(storage_path("app/uploads/stories/{$filename}"), $filename);
    }
}
