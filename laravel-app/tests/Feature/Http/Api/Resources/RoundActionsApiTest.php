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
}
