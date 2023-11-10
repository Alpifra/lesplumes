<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Story extends Model
{
    use HasFactory;

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
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, foreignKey: 'media_id', ownerKey: 'id');
    }
}
