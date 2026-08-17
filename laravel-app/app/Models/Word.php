<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'word',
        'category',
    ];

    /**
     * Draw a word for a session to come.
     *
     * A word already played would rob the circle of a first time, so the
     * words of past sessions are set aside — along with the one the plume
     * is looking at, so that asking again always changes the answer.
     *
     * The circle would have to play twenty-two thousand sessions to exhaust
     * the dictionary; should it ever happen, repeating a word beats handing
     * back nothing at all.
     */
    public static function draw(?string $exclude = null): ?string
    {
        $played = Round::query()->select('word');

        return static::pick($exclude, $played) ?? static::pick($exclude);
    }

    /**
     * One word at random, skipping `$exclude` and, when given, every word of
     * the `$played` subquery.
     */
    private static function pick(?string $exclude, $played = null): ?string
    {
        return static::query()
            ->when($played, fn ($query) => $query->whereNotIn('word', $played))
            ->when(filled($exclude), fn ($query) => $query->where('word', '!=', $exclude))
            ->inRandomOrder()
            ->value('word');
    }
}
