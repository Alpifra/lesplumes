<?php

namespace Tests\Feature\Models;

use App\Models\Media;
use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * @group model
     * @group story
     */
    public function create_model()
    {
        $story = new Story;
        $round = Round::factory(1)->createOne();
        $writer = User::factory(1)->createOne();

        $story->round()->associate($round);
        $story->writer()->associate($writer);

        self::assertNull($story->id);

        $story->save();

        self::assertNotNull($story->id);
        self::assertEquals($round->id, $story->round->id);
        self::assertEquals($writer->id, $story->writer->id);
        self::assertNotNull($story->created_at);
        self::assertNotNull($story->updated_at);
    }

    /**
     * @test
     * @group model
     * @group story
     */
    public function edit_model()
    {
        Story::factory(1)
            ->has(User::factory()->count(1), 'writer')
            ->has(Round::factory()->count(1), 'round')
            ->has(Media::factory()->count(1), 'media')
            ->create();

        $story = Story::all()->first();
        $round = Round::factory(1)->createOne();
        $writer = User::factory(1)->createOne();

        $story->round()->associate($round);
        $story->writer()->associate($writer);

        $story->save();

        self::assertEquals($round->id, $story->round->id);
        self::assertEquals($writer->id, $story->writer->id);
        self::assertNotNull($story->created_at);
    }

    /**
     * @test
     * @group model
     * @group story
     */
    public function delete_model()
    {
        Story::factory(1)
            ->for(Round::factory()->create())
            ->for(User::factory()->create(), 'writer')
            ->has(Media::factory(1))
            ->create();
        $story = Story::all()->first();
        $story->delete();

        self::assertEmpty(Story::all());
        self::assertEmpty(Media::all());
        self::assertNotEmpty(User::all());
        self::assertNotEmpty(Round::all());
    }
}
