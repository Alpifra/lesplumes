<?php

namespace Tests\Feature;

use App\Mail\Invitation;
use App\Mail\ResetPassword;
use App\Mail\SessionClosed;
use App\Mail\SessionOpened;
use App\Models\Media;
use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Database\Factories\MediaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    /**
     * @test
     * @group mail
     */
    public function opening_a_session_mails_each_convened_plume(): void
    {
        $master = User::factory()->create();
        $plumes = User::factory(2)->create();
        Round::factory()->create(['next_master_id' => $master->id]);

        $this->actingAs($master)->postJson('/api/rounds', [
            'word'         => 'lanterne',
            'master'       => $master->id,
            'participants' => $plumes->pluck('id')->all(),
        ])->assertStatus(201);

        Mail::assertSent(SessionOpened::class, 2);
        foreach ($plumes as $plume) {
            Mail::assertSent(SessionOpened::class, fn ($mail) => $mail->hasTo($plume->email) && $mail->round->word === 'lanterne');
        }
        Mail::assertNotSent(SessionOpened::class, fn ($mail) => $mail->hasTo($master->email));
    }

    /**
     * @test
     * @group mail
     */
    public function the_last_deposit_announces_the_end_to_the_whole_session_once(): void
    {
        [$writer, $joker] = User::factory(2)->create();
        $round = Round::factory()->create(['start_at' => now()->subDay(), 'end_at' => null]);
        $round->participants()->attach([$writer->id => ['joker_at' => null], $joker->id => ['joker_at' => now()]]);
        $story = Story::factory()->for($round)->create(['writer_id' => $writer->id]);

        $this->actingAs($writer)
            ->post("/api/stories/{$story->id}/media", ['file' => MediaFactory::createFile()])
            ->assertStatus(201);

        Mail::assertSent(SessionClosed::class, 3);
        foreach ([$writer, $joker, $round->master] as $plume) {
            Mail::assertSent(SessionClosed::class, fn ($mail) => $mail->hasTo($plume->email));
        }

        // The joker shows in the recap, struck through.
        Mail::assertSent(SessionClosed::class, function ($mail) use ($joker) {
            $mail->assertSeeInHtml('<s>' . e("{$joker->first_name} {$joker->last_name}") . '</s> (joker)', false);

            return true;
        });

        $this->assertFalse($round->fresh()->announceClosure());
        Mail::assertSent(SessionClosed::class, 3);
    }

    /**
     * @test
     * @group mail
     */
    public function a_session_reaching_its_deadline_is_announced_by_the_scheduler(): void
    {
        $due = Round::factory()->create(['start_at' => now()->subWeek(), 'end_at' => now()->subMinute()]);
        $due->participants()->attach(User::factory()->create());
        $open = Round::factory()->create(['start_at' => now()->subWeek(), 'end_at' => now()->addDay()]);
        $open->participants()->attach(User::factory()->create());

        $this->artisan('rounds:announce-closures')->assertSuccessful();
        $this->artisan('rounds:announce-closures')->assertSuccessful();

        // The participant and the master, once despite the second run.
        $this->assertCount(2, Mail::sent(SessionClosed::class, fn ($mail) => $mail->round->is($due)));
        Mail::assertNotSent(SessionClosed::class, fn ($mail) => $mail->round->is($open));
        $this->assertNotNull($due->fresh()->closure_announced_at);
    }

    /**
     * @test
     * @group mail
     */
    public function an_outsider_is_invited_by_mail(): void
    {
        $inviter = User::factory()->create();

        $this->actingAs($inviter)
            ->postJson('/api/invitations', ['email' => 'nouvelle@lesplumes.test'])
            ->assertJsonPath('status', 'invited');

        Mail::assertSent(Invitation::class, fn ($mail) => $mail->hasTo('nouvelle@lesplumes.test') && $mail->inviter->is($inviter));
    }

    /**
     * @test
     * @group mail
     */
    public function a_forgotten_password_mails_a_link_to_the_front(): void
    {
        $plume = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => $plume->email])->assertSuccessful();

        Mail::assertSent(ResetPassword::class, function ($mail) use ($plume) {
            $mail->assertSeeInHtml(config('app.frontend_url') . '/mot-de-passe/nouveau?token=', false);

            return $mail->hasTo($plume->email);
        });
    }

    /**
     * @test
     * @group mail
     */
    public function every_mail_renders_in_the_style_of_the_circle(): void
    {
        $plume = User::factory()->create(['first_name' => 'Jeanne']);
        $round = Round::factory()->create(['word' => 'lanterne', 'end_at' => now()->subHour()]);
        $story = Story::factory()->for($round)->create(['title' => 'La nuit tombe']);
        Media::storeForStory($story, MediaFactory::createFile());

        $mails = [
            new SessionOpened($round, $plume),
            new SessionClosed($round, $plume),
            new Invitation('nouvelle@lesplumes.test', $plume, $round),
            new ResetPassword($plume, 'jeton'),
        ];

        foreach ($mails as $mail) {
            $mail->assertSeeInHtml('Les Plumes');
            $mail->assertSeeInHtml('À vos plumes !');
        }

        $mails[0]->assertSeeInHtml('lanterne');
        $mails[1]->assertSeeInHtml('La nuit tombe');
        $mails[2]->assertSeeInHtml('Créer mon compte');
    }
}
