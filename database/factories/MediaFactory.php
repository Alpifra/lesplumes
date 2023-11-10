<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

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
        $name = uniqid();
        $extension = '.pdf';
        $fake_pdf = file_get_contents(storage_path('framework/testing/fake.pdf'));
        $target_disk = Config::get('mediable.default_disk');
        Storage::disk($target_disk)->put($name . $extension, $fake_pdf);

        return [
            'disk'           => $target_disk,
            'directory'      => 'stories',
            'filename'       => $name,
            'extension'      => $extension,
            'mime_type'      => 'application/pdf',
            'aggregate_type' => $extension,
            'size'           => strlen($fake_pdf),
        ];
    }
}
