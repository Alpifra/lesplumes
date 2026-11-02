<?php

namespace App\Console\Commands;

use App\Models\Round;
use Illuminate\Console\Command;

class AnnounceClosedRounds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rounds:announce-closures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Annonce par mail la fin des sessions arrivées à échéance";

    /**
     * A session closed by the last deposit is announced right away; one
     * given a deadline closes on its own, with nobody to trigger the mail.
     * The scheduler runs this to catch those.
     */
    public function handle(): int
    {
        $announced = Round::query()
            ->whereNull('closure_announced_at')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', now())
            ->get()
            ->filter(fn (Round $round) => $round->announceClosure())
            ->count();

        $this->info("{$announced} session(s) annoncée(s).");

        return self::SUCCESS;
    }
}
