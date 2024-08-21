<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Plank\Mediable\Media as MediableMedia;

class Media extends MediableMedia
{
    use HasFactory;

    public const STORY_MEDIA_DIRECTORY = 'stories';

    protected $table = 'medias';

    /**
     * Validate an incoming request
     */
    public static function validate(Request $request): Request
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:1024'
        ]);

        return $request;
    }

    /**
     * The story attached to a media.
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }
}
