<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;

class CountStoryCharacters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:count-characters
                            {--all : Recompte aussi les textes déjà comptés}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compte les caractères des PDF déposés, pour le classement blabla';

    /**
     * Texts are counted on deposit: this catches up on those deposited
     * before, or imported with the history.
     */
    public function handle(): int
    {
        $medias = Media::query()
            ->whereNotNull('story_id')
            ->when(!$this->option('all'), fn ($query) => $query->whereNull('characters'))
            ->get();

        $unreadable = 0;

        $this->withProgressBar($medias, function (Media $media) use (&$unreadable) {
            if ($media->countCharacters() === null) {
                $unreadable++;
            }
        });

        $this->newLine();
        $this->info("{$medias->count()} texte(s) parcouru(s), {$unreadable} sans texte lisible.");

        return self::SUCCESS;
    }
}
