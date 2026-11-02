<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Story extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'round_id',
        'writer_id',
        'title',
    ];

    /**
     * Days the writer took to hand the text in: from the opening of the
     * session to the deposit (story.updated_at). Null for a session that
     * never recorded its start.
     */
    public function writingDays(): ?float
    {
        if (!$this->round?->start_at) {
            return null;
        }

        $seconds = $this->updated_at->getTimestamp() - $this->round->start_at->getTimestamp();

        return max(0, $seconds / 86400);
    }

    /**
     * The round attached to a story.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class, foreignKey: 'round_id', ownerKey: 'id');
    }

    /**
     * The writer attached to a story.
     */
    public function writer(): BelongsTo
    {
        return $this->belongsTo(User::class, foreignKey: 'writer_id', ownerKey: 'id');
    }

    /**
     * The media attached to a story.
     */
    public function media(): HasOne
    {
        return $this->hasOne(Media::class);
    }
}
