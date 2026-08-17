<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\User;
use App\Models\Word;
use Tests\TestCase;

class WordApiTest extends TestCase
{
    /**
     * The suite shares a persistent database whose dictionary is not under
     * its control, so these tests check the endpoint is wired — what the
     * draw may and may not hand back is pinned in `WordTest`.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (Word::query()->doesntExist()) {
            Word::insert([['word' => 'zinzolin', 'category' => 'nom']]);
        }
    }

    /**
     * @test
     * @group api
     * @group word
     */
    public function a_word_is_drawn_for_the_session_to_come(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/words/random')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['word']]);

        self::assertTrue(
            Word::query()->where('word', $response->json('data.word'))->exists(),
            'Le mot tiré ne vient pas du dictionnaire.',
        );
    }

    /**
     * @test
     * @group api
     * @group word
     */
    public function the_dictionary_is_closed_to_a_visitor(): void
    {
        $this->getJson('/api/words/random')->assertStatus(401);
    }

    /**
     * @test
     * @group api
     * @group word
     */
    public function an_oversized_exclusion_is_refused(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/api/words/random?exclude=' . str_repeat('a', 51))
            ->assertStatus(422)
            ->assertJsonValidationErrors('exclude');
    }
}
