<?php

namespace Tests\Feature\Models;

use App\Models\Round;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A dictionary small enough to reason about: the draw is random, so the
     * assertions bear on what it may never hand back.
     */
    private function dictionary(array $words): void
    {
        // The seeder loads the real dictionary, and this suite shares its
        // database: the draw is only predictable on a table we emptied.
        Word::query()->delete();

        Word::insert(array_map(
            fn (string $word) => ['word' => $word, 'category' => 'nom'],
            $words,
        ));
    }

    /** How many draws it takes before "it never happens" means something. */
    private const DRAWS = 60;

    private function draws(?string $exclude = null): array
    {
        return array_map(fn () => Word::draw($exclude), range(1, self::DRAWS));
    }

    /**
     * @test
     * @group model
     * @group word
     */
    public function draw_takes_a_word_of_the_dictionary(): void
    {
        $this->dictionary(['zinzolin', 'fanfreluche', 'chafouin']);

        self::assertEmpty(array_diff($this->draws(), ['zinzolin', 'fanfreluche', 'chafouin']));
    }

    /**
     * @test
     * @group model
     * @group word
     */
    public function draw_sets_aside_the_words_already_played(): void
    {
        $this->dictionary(['zinzolin', 'fanfreluche', 'chafouin']);
        Round::factory()->create(['word' => 'fanfreluche']);
        Round::factory()->create(['word' => 'chafouin']);

        self::assertSame(['zinzolin'], array_unique($this->draws()));
    }

    /**
     * The plume asks again because the word does not suit her: handing back
     * the same one would read as a broken button.
     *
     * @test
     * @group model
     * @group word
     */
    public function draw_never_hands_back_the_word_on_screen(): void
    {
        $this->dictionary(['zinzolin', 'fanfreluche']);

        self::assertSame(['fanfreluche'], array_unique($this->draws('zinzolin')));
    }

    /**
     * @test
     * @group model
     * @group word
     */
    public function draw_repeats_a_played_word_rather_than_handing_back_nothing(): void
    {
        $this->dictionary(['zinzolin']);
        Round::factory()->create(['word' => 'zinzolin']);

        self::assertSame('zinzolin', Word::draw());
    }

    /**
     * @test
     * @group model
     * @group word
     */
    public function draw_has_nothing_to_offer_on_an_empty_dictionary(): void
    {
        $this->dictionary([]);

        self::assertNull(Word::draw());
    }

    /**
     * MySQL collates without regard for accents, so the dictionary cannot
     * rely on a unique index to keep "âcre" apart from "acre".
     *
     * @test
     * @group model
     * @group word
     */
    public function the_dictionary_keeps_both_forms_of_an_accented_word(): void
    {
        $this->dictionary(['acre', 'âcre']);

        self::assertSame(2, Word::count());
    }
}
