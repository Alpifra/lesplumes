<?php

namespace App\Console\Commands;

use App\Models\Word;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

class ImportFrenchWords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'words:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Charge le dictionnaire français dans lequel est tiré le mot d'une session";

    /**
     * Rows inserted per query. The file holds some twenty thousand words:
     * one query each would take minutes, a single one would exceed the
     * placeholder limit.
     */
    private const CHUNK = 500;

    /**
     * Replace the dictionary with the contents of
     * `database/data/french-words.tsv`.
     *
     * The table is emptied first, which makes the command replayable and
     * keeps it the single source of truth: the file says what the dictionary
     * holds, nothing accumulates behind it.
     *
     * The file is an adaptation of Lexique 3.83 (CC BY-SA 4.0), regenerated
     * from `Lexique383.tsv` — see `database/data/README.md` — with:
     *
     *   awk -F'\t' 'NR>1 && $14==1 && $6!="p" &&
     *     ($4=="NOM" || $4=="ADJ" || $4=="VER" || $4=="ADV") &&
     *     $1 ~ /^[a-zàâäéèêëîïôöùûüÿçœæ]+$/ &&
     *     length($1)>=4 && length($1)<=16 {
     *       freq = ($8>$7 ? $8 : $7)
     *       if (freq < 0.5 || seen[$1]++) next
     *       cat = ($4=="NOM" ? "nom" : $4=="ADJ" ? "adjectif"
     *            : $4=="VER" ? "verbe" : "adverbe")
     *       print $1 "\t" cat
     *     }' Lexique383.tsv | sort > database/data/french-words.tsv
     */
    public function handle(): int
    {
        $path = database_path('data/french-words.tsv');

        if (!is_readable($path)) {
            $this->error("Dictionnaire introuvable : {$path}");

            return self::FAILURE;
        }

        // Deleted rather than truncated: TRUNCATE commits implicitly in
        // MySQL, which would blow up the transaction a seeding test runs in.
        Word::query()->delete();

        $imported = 0;

        // The file is read line by line: loading twenty thousand rows in
        // memory to hand them straight to the database would be wasteful.
        LazyCollection::make(function () use ($path) {
            $handle = fopen($path, 'r');

            while (($line = fgets($handle)) !== false) {
                yield $line;
            }

            fclose($handle);
        })
            ->map(function (string $line): ?array {
                [$word, $category] = array_pad(explode("\t", trim($line), 2), 2, null);

                return filled($word) && filled($category)
                    ? ['word' => $word, 'category' => $category]
                    : null;
            })
            ->filter()
            ->chunk(self::CHUNK)
            ->each(function (LazyCollection $chunk) use (&$imported): void {
                $rows = $chunk->all();

                Word::insert($rows);

                $imported += count($rows);
            });

        $this->info("{$imported} mots dans le dictionnaire.");

        return self::SUCCESS;
    }
}
