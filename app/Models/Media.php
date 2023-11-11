<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Plank\Mediable\Media as MediableMedia;

class Media extends MediableMedia
{
    use HasFactory;

    public const STORY_MEDIA_DIRECTORY = 'stories';

    protected $table = 'medias';
}
