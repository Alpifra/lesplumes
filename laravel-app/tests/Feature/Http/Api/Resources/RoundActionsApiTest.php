<?php

namespace Tests\Feature\Http\Api\Resources;

use App\Models\Media;
use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Database\Factories\MediaFactory;
use Tests\TestCase;

class RoundActionsApiTest extends TestCase
{
    private function activeRound(): Round
    {
        return Round::factory()->create([
            'start_at' => now()->subWeek(),
            'end_at'   => now()->addWeek(),
        ]);
    }

    /** A session with no deadline: it closes on the last deposit alone. */
    private function openRound(): Round
    {
        return Round::factory()->create([
            'start_at' => now()->subWeek(),
            'end_at'   => null,
        ]);
    }

    private function finishedRound(): Round
    {
        return Round::factory()->create([
            'start_at' => now()->subMonth(),
            'end_at'   => now()->subWeek(),
        ]);
    }

    /**
     * @test
     * @group api
     * @group story
     */
    public function participant_can_deposit_a_plume_on_an_active_round(): void
    {
        $user = User::factory()->create();
        $round = $this->activeRound();
        $round->participants()->attach($user);

        $response = $this->actingAs($user)->post("/api/rounds/{$round->id}/stories", [
            'title' => 'Ma rédaction',
            'file'  => MediaFactory::createFile(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Ma rédaction')
            ->assertJsonPath('data.writer.id', $user->id);

        $this->assertDatabaseHas('stories', [
            'round_id'  => $round->id,
            'writer_id' => $user->id,
            'title'     => 'Ma rédaction',
        ]);
    }

    /**
     * @test
     * @group api
     * @group story
     */
    public function deposit_is_blocked_on_a_finished_round(): void
    {
        $user = User::factory()->create();
        $round = $this->finishedRound();
        $round->participants()->attach($user);

        $response = $this->actingAs($user)->post("/api/rounds/{$round->id}/stories", [
            'title' => 'Trop tard',
            'file'  => MediaFactory::createFile(),
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('stories', ['round_id' => $round->id]);
    }

    /**
     * @test
     * @group api
     * @group story
     */
    public function a_non_participant_cannot_deposit(): void
    {
        $user = User::factory()->create();
        $round = $this->activeRound();

        $response = $this->actingAs($user)->post("/api/rounds/{$round->id}/stories", [
            'title' => 'Intrus',
            'file'  => MediaFactory::createFile(),
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     * @group api
     * @group media
     */
    public function a_story_pdf_can_be_downloaded(): void
    {
        $user = User::factory()->create();
        $round = $this->activeRound();
        $round->participants()->attach($user);
        $story = Story::factory()->for($round)->create();
        Media::storeForStory($story, MediaFactory::createFile());

        $response = $this->actingAs($user)
            ->get("/api/rounds/{$round->id}/stories/{$story->id}/download");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function a_round_can_be_downloaded_as_a_zip(): void
    {
        $user = User::factory()->create();
        $round = $this->activeRound();
        $round->participants()->attach($user);
        $story = Story::factory()->for($round)->create();
        Media::storeForStory($story, MediaFactory::createFile());

        $response = $this->actingAs($user)->get("/api/rounds/{$round->id}/download");

        $response->assertStatus(200);
        $this->assertStringContainsString('zip', strtolower($response->headers->get('content-type')));
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function an_outsider_cannot_download_a_round(): void
    {
        $intruder = User::factory()->create();
        $round = $this->activeRound();
        $story = Story::factory()->for($round)->create();
        Media::storeForStory($story, MediaFactory::createFile());

        $this->actingAs($intruder)
            ->get("/api/rounds/{$round->id}/download")
            ->assertStatus(403);

        $this->actingAs($intruder)
            ->get("/api/rounds/{$round->id}/stories/{$story->id}/download")
            ->assertStatus(403);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function master_can_invite_an_existing_user_as_participant(): void
    {
        $master = User::factory()->create();
        $round = Round::factory()->create([
            'master_id' => $master->id,
            'start_at'  => now()->subWeek(),
            'end_at'    => now()->addWeek(),
        ]);
        $invitee = User::factory()->create();

        $response = $this->actingAs($master)->postJson("/api/rounds/{$round->id}/invite", [
            'email' => $invitee->email,
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'attached');
        $this->assertTrue($round->participants()->where('users.id', $invitee->id)->exists());
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function inviting_an_unknown_email_returns_invited_status(): void
    {
        $master = User::factory()->create();
        $round = Round::factory()->create(['master_id' => $master->id]);

        $response = $this->actingAs($master)->postJson("/api/rounds/{$round->id}/invite", [
            'email' => 'nouvelle-plume@example.com',
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'invited');
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function only_the_master_can_invite(): void
    {
        $round = Round::factory()->create();
        $intruder = User::factory()->create();

        $response = $this->actingAs($intruder)->postJson("/api/rounds/{$round->id}/invite", [
            'email' => 'someone@example.com',
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     * @group api
     * @group user
     */
    public function a_global_invitation_reports_an_existing_member(): void
    {
        $inviter = User::factory()->create();
        $member = User::factory()->create();

        $response = $this->actingAs($inviter)->postJson('/api/invitations', [
            'email' => $member->email,
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'exists');
    }

    /**
     * @test
     * @group api
     * @group user
     */
    public function a_global_invitation_to_an_unknown_email_is_sent(): void
    {
        $inviter = User::factory()->create();

        $response = $this->actingAs($inviter)->postJson('/api/invitations', [
            'email' => 'nouvelle@example.com',
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'invited');
    }

    /**
     * The master of the round never writes here, which is the point: the
     * session closes on the participants alone.
     *
     * @test
     * @group api
     * @group round
     */
    public function a_session_closes_once_every_plume_has_handed_in(): void
    {
        $first = User::factory()->create();
        $last = User::factory()->create();
        $round = $this->openRound();
        $round->participants()->attach([$first->id, $last->id]);

        $this->actingAs($first)->post("/api/rounds/{$round->id}/stories", [
            'file' => MediaFactory::createFile(),
        ])->assertStatus(201);

        // One plume short: the session stays open.
        $round->refresh();
        self::assertNull($round->end_at);
        self::assertSame('en-cours', $round->status);

        $this->actingAs($last)->post("/api/rounds/{$round->id}/stories", [
            'file' => MediaFactory::createFile(),
        ])->assertStatus(201);

        $round->refresh();
        self::assertNotNull($round->end_at);
        self::assertSame('termine', $round->status);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function a_closed_session_refuses_any_further_deposit(): void
    {
        $plume = User::factory()->create();
        $round = $this->openRound();
        $round->participants()->attach($plume);

        $this->actingAs($plume)->post("/api/rounds/{$round->id}/stories", [
            'file' => MediaFactory::createFile(),
        ])->assertStatus(201);

        self::assertSame('termine', $round->fresh()->status);

        $this->actingAs($plume)->post("/api/rounds/{$round->id}/stories", [
            'file' => MediaFactory::createFile(),
        ])->assertStatus(403);
    }

    /**
     * The turn is pinned with a hand-off rather than left to the rotation:
     * the suite shares a persistent database, where the circle and the
     * sessions that precede a test are not under its control.
     */
    private function roundHandingTheTurnTo(User $plume): Round
    {
        return Round::factory()->create([
            'start_at'       => now()->subMonth(),
            'end_at'         => now()->subWeek(),
            'next_master_id' => $plume->id,
        ]);
    }

    /**
     * Plumes that follow one another in the circle. They share a first name
     * no one else bears, so whatever the persistent database holds, none of
     * its plumes can slip between them: their last names set their order.
     *
     * @return User[]
     */
    private function neighbours(int $count): array
    {
        $firstName = 'Voisine' . uniqid();

        return array_map(
            fn (string $lastName) => User::factory()->create(['first_name' => $firstName, 'last_name' => $lastName]),
            array_slice(['Andre', 'Bertin', 'Colin'], 0, $count),
        );
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function the_next_session_names_the_plume_whose_turn_it_is(): void
    {
        $plume = User::factory()->create();
        $round = $this->roundHandingTheTurnTo($plume);

        $this->actingAs($plume)->getJson('/api/rounds/next')
            ->assertStatus(200)
            ->assertJsonPath('data.selector.id', $plume->id)
            ->assertJsonPath('data.previous_round.id', $round->id)
            ->assertJsonStructure(['data' => ['selector', 'plumes' => ['data'], 'previous_round']]);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function the_plume_in_turn_can_hand_the_word_over(): void
    {
        [$plume, $successor] = $this->neighbours(2);
        $round = $this->roundHandingTheTurnTo($plume);

        $this->actingAs($plume)->postJson('/api/rounds/hand-off', ['plume' => $successor->id])
            ->assertStatus(200)
            ->assertJsonPath('data.id', $successor->id);

        $this->assertDatabaseHas('rounds', [
            'id'             => $round->id,
            'next_master_id' => $successor->id,
        ]);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function handing_the_word_over_is_refused_to_a_plume_whose_turn_it_is_not(): void
    {
        $plume = User::factory()->create();
        $intruder = User::factory()->create();
        $round = $this->roundHandingTheTurnTo($plume);

        $this->actingAs($intruder)->postJson('/api/rounds/hand-off', ['plume' => $intruder->id])
            ->assertStatus(403);

        $this->assertDatabaseHas('rounds', [
            'id'             => $round->id,
            'next_master_id' => $plume->id,
        ]);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function the_next_session_leaves_deactivated_plumes_out_of_the_circle(): void
    {
        $plume = User::factory()->create();
        $gone = User::factory()->create();
        $gone->deactivate();
        $this->roundHandingTheTurnTo($plume);

        $plumes = $this->actingAs($plume)->getJson('/api/rounds/next')
            ->assertStatus(200)
            ->json('data.plumes.data.*.id');

        self::assertContains($plume->id, $plumes);
        self::assertNotContains($gone->id, $plumes);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function handing_the_word_over_to_a_deactivated_plume_is_refused(): void
    {
        $plume = User::factory()->create();
        $gone = User::factory()->create();
        $gone->deactivate();
        $round = $this->roundHandingTheTurnTo($plume);

        $this->actingAs($plume)->postJson('/api/rounds/hand-off', ['plume' => $gone->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['plume']);

        $this->assertDatabaseHas('rounds', [
            'id'             => $round->id,
            'next_master_id' => $plume->id,
        ]);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function a_session_cannot_be_opened_with_a_deactivated_plume(): void
    {
        $plume = User::factory()->create();
        $guest = User::factory()->create();
        $gone = User::factory()->create();
        $gone->deactivate();
        $this->roundHandingTheTurnTo($plume);

        $this->actingAs($plume)->postJson('/api/rounds', [
            'word'         => 'wagon',
            'master'       => $plume->id,
            'participants' => [$guest->id, $gone->id],
            'start_at'     => now()->toDateTimeString(),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['participants.1']);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function a_deactivated_plume_cannot_be_invited_to_a_session(): void
    {
        $round = $this->activeRound();
        $gone = User::factory()->create();
        $gone->deactivate();

        $this->actingAs($round->master)->postJson("/api/rounds/{$round->id}/invite", ['email' => $gone->email])
            ->assertStatus(422);

        self::assertFalse($round->participants()->where('users.id', $gone->id)->exists());
    }

    /**
     * @test
     * @group api
     * @group user
     */
    public function a_global_invitation_reports_a_deactivated_member(): void
    {
        $user = User::factory()->create();
        $gone = User::factory()->create();
        $gone->deactivate();

        $this->actingAs($user)->postJson('/api/invitations', ['email' => $gone->email])
            ->assertStatus(200)
            ->assertJsonPath('status', 'deactivated');
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function the_word_only_passes_to_the_next_plume(): void
    {
        [$plume, , $further] = $this->neighbours(3);
        $round = $this->roundHandingTheTurnTo($plume);

        $this->actingAs($plume)->postJson('/api/rounds/hand-off', ['plume' => $further->id])
            ->assertStatus(422);

        $this->assertDatabaseHas('rounds', [
            'id'             => $round->id,
            'next_master_id' => $plume->id,
        ]);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function the_word_can_be_passed_on_along_the_circle(): void
    {
        [$plume, $successor, $third] = $this->neighbours(3);
        $round = $this->roundHandingTheTurnTo($plume);

        $this->actingAs($plume)->postJson('/api/rounds/hand-off', ['plume' => $successor->id])
            ->assertStatus(200);

        $this->actingAs($successor)->postJson('/api/rounds/hand-off', ['plume' => $third->id])
            ->assertStatus(200);

        $this->assertDatabaseHas('rounds', [
            'id'             => $round->id,
            'next_master_id' => $third->id,
        ]);
    }

    /**
     * @test
     * @group api
     * @group round
     */
    public function the_next_session_lists_the_circle_in_rotation_order(): void
    {
        [$plume, $successor, $third] = $this->neighbours(3);
        $this->roundHandingTheTurnTo($plume);

        $ids = $this->actingAs($plume)->getJson('/api/rounds/next')
            ->assertStatus(200)
            ->json('data.plumes.data.*.id');

        $positions = array_map(fn (User $p) => array_search($p->id, $ids, true), [$plume, $successor, $third]);

        self::assertSame([$positions[0], $positions[0] + 1, $positions[0] + 2], $positions);
    }
}
