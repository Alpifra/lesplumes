<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Media;
use App\Models\Round;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /** Number of plumes in the circle, besides the admin. */
    private const PLUMES = 10;

    /** Number of closed sessions, on top of the ongoing one. */
    private const FINISHED_ROUNDS = 9;

    /**
     * Seed the application's database.
     *
     * The set is built for local development: one admin who masters every
     * session — so his dashboard is populated — and a circle of plumes who
     * take part in all of them.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'first_name' => 'Alexandre',
            'last_name'  => 'Chauvin',
            'user_name'  => 'Alpifra',
            'email'      => 'admin@email.com',
            'password'   => Hash::make('password'),
        ]);

        $plumes = $this->plumes();

        // Two-week sessions laid end to end, the most recent one first.
        for ($k = 1; $k <= self::FINISHED_ROUNDS; $k++) {
            $endAt = now()->subWeeks(2 * $k);

            $round = $this->round($admin, $plumes, $endAt->copy()->subWeeks(2), $endAt);

            // A closed session everyone answered.
            $this->deposit($round, $plumes);
        }

        $ongoing = $this->round($admin, $plumes, now()->subWeek(), now()->addWeek());

        // Half the circle has handed in so far, the others are still writing.
        $this->deposit($ongoing, $plumes->take(intdiv($plumes->count(), 2)));
    }

    /**
     * The circle of plumes, with readable credentials: every account logs in
     * with the password "password".
     */
    private function plumes(): Collection
    {
        return collect(range(1, self::PLUMES))->map(function (): User {
            $handle = fake()->unique()->userName();

            return User::factory()->create([
                'first_name' => fake()->firstName(),
                'last_name'  => fake()->lastName(),
                'user_name'  => $handle,
                'email'      => $handle . '@lesplumes.test',
            ]);
        });
    }

    /**
     * A session mastered by the admin, which the whole circle joins.
     */
    private function round(User $admin, Collection $plumes, Carbon $startAt, Carbon $endAt): Round
    {
        $round = Round::factory()->create([
            'master_id'  => $admin->id,
            'start_at'   => $startAt,
            'end_at'     => $endAt,
            // Keeps the sessions list in chronological order.
            'created_at' => $startAt,
        ]);

        $round->participants()->attach($plumes->pluck('id'));

        return $round;
    }

    /**
     * Attach a text and its PDF for each given plume, written somewhere
     * between the opening and the deadline of the session so the deposit
     * dates and the writing delay stay believable.
     */
    private function deposit(Round $round, Collection $plumes): void
    {
        $plumes->each(function (User $plume) use ($round): void {
            $writtenAt = fake()->dateTimeBetween(
                $round->start_at,
                min($round->end_at, now()),
            );

            Story::factory()
                ->for($round)
                ->for($plume, 'writer')
                ->has(Media::factory())
                ->create([
                    'created_at' => $writtenAt,
                    'updated_at' => $writtenAt,
                ]);
        });
    }
}
