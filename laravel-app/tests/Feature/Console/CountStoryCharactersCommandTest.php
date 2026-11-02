<?php

namespace Tests\Feature\Console;

use App\Models\Media;
use App\Models\Story;
use Database\Factories\MediaFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountStoryCharactersCommandTest extends TestCase
{
    #[Test]
    #[Group('console')]
    #[Group('media')]
    public function the_characters_of_a_text_are_counted_on_deposit(): void
    {
        $media = Media::storeForStory(Story::factory()->create(), MediaFactory::createFile());

        $this->assertSame(35, $media->fresh()->characters);
    }

    #[Test]
    #[Group('console')]
    #[Group('media')]
    public function the_texts_not_counted_yet_are_counted(): void
    {
        $media = Media::storeForStory(Story::factory()->create(), MediaFactory::createFile());
        $media->update(['characters' => null]);

        $this->artisan('stories:count-characters')->assertSuccessful();

        $this->assertSame(35, $media->fresh()->characters);
    }
}
