<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class Round extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'word',
        'start_at',
        'end_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'start_at'   => 'datetime',
        'end_at'     => 'datetime',
    ];

    /**
     * Validate an incoming request
     */
    public static function validate(Request $request): Request
    {
        $request->validate([
            'word'           => 'required|string|max:50',
            'master'         => 'required|exists:\App\Models\User,id',
            'participants'   => 'required|array',
            'participants.*' => 'different:master|exists:\App\Models\User,id',
            'start_at'       => 'nullable|date',
            'end_at'         => 'nullable|date|after_or_equal:start_at',
        ]);

        return $request;
    }

    /**
     * The derived status of the round.
     *
     * A session closes when the circle has handed everything in, which
     * stamps end_at with that moment (see closeIfComplete). A round is
     * therefore "en-cours" until end_at is set and reached — sessions given
     * a deadline up front read the same way — and "termine" from there on.
     */
    public function getStatusAttribute(): string
    {
        if ($this->end_at && !$this->end_at->isFuture()) {
            return 'termine';
        }

        return 'en-cours';
    }

    /**
     * Close the session if every plume has handed her text in.
     *
     * A text counts as handed in once a file is attached to it. Only the
     * participants are awaited: the master is the plume who picked the word.
     * Returns whether this deposit was the one that closed the session.
     */
    public function closeIfComplete(): bool
    {
        if ($this->end_at) {
            return false;
        }

        $awaited = $this->participants()->pluck('users.id');

        if ($awaited->isEmpty()) {
            return false;
        }

        $handedIn = $this->roundStories()
            ->whereHas('media')
            ->whereIn('writer_id', $awaited)
            ->pluck('writer_id')
            ->unique();

        if ($handedIn->count() < $awaited->count()) {
            return false;
        }

        $this->end_at = now();
        $this->save();

        return true;
    }

    /**
     * The most recent session of the circle, ordered as the sessions list is.
     */
    public static function mostRecent(): ?self
    {
        return static::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * The plume whose turn it is to pick the word of the next session.
     *
     * The turn travels through the circle by ascending id and starts over at
     * the first plume once the last one has played — unless the plume in turn
     * handed it over, which the session she closed records.
     */
    public static function nextSelector(): ?User
    {
        $firstPlume = User::query()->orderBy('id')->first();
        $last = static::mostRecent();

        if (!$last) {
            return $firstPlume;
        }

        if ($last->next_master_id) {
            return $last->nextMaster;
        }

        // A master whose account is gone leaves no place in the circle to
        // resume from, so the turn goes back to the first plume.
        if (!$last->master_id) {
            return $firstPlume;
        }

        return User::query()->where('id', '>', $last->master_id)->orderBy('id')->first()
            ?? $firstPlume;
    }

    /**
     * The master attached to a round.
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, foreignKey: 'master_id', ownerKey: 'id');
    }

    /**
     * The plume the master handed the next word-picking over to, if any.
     */
    public function nextMaster(): BelongsTo
    {
        return $this->belongsTo(User::class, foreignKey: 'next_master_id', ownerKey: 'id');
    }

    /**
     * The stories attached to a round.
     */
    public function roundStories(): HasMany
    {
        return $this->hasMany(related: Story::class, foreignKey: 'round_id', localKey: 'id');
    }

    /**
     * Get the participants that belong to the round.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
