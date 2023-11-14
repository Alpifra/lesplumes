<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plank\Mediable\Media as MediableMedia;

class Media extends MediableMedia
{
    use HasFactory;

    public const STORY_MEDIA_DIRECTORY = 'stories';

    protected $table = 'medias';

    /**
     * The story attached to a media.
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }
}
