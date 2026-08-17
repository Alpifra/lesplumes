<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Plank\Mediable\Media as MediableMedia;
use Smalot\PdfParser\Parser;
use Throwable;

class Media extends MediableMedia
{
    use HasFactory;

    public const STORY_MEDIA_DIRECTORY = 'stories';

    public const STORY_MEDIA_DISK = 'medias';

    protected $table = 'medias';

    /**
     * Validate an incoming request
     */
    public static function validate(Request $request): Request
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:2048'
        ]);

        return $request;
    }

    /**
     * Persist an uploaded PDF for a story, creating or replacing the
     * story's single attached media. Centralises the file bookkeeping so
     * both StoryController and MediaController stay consistent.
     */
    public static function storeForStory(Story $story, UploadedFile $file): self
    {
        $media = $story->media ?? new self;

        $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'pdf');
        // Keep a stable filename across replacements so the stored file is overwritten.
        $filename = $media->exists ? $media->filename : md5($story->id . '-' . uniqid('', true));

        $media->disk           = self::STORY_MEDIA_DISK;
        $media->directory      = self::STORY_MEDIA_DIRECTORY;
        $media->filename       = $filename;
        $media->extension      = $extension;
        $media->mime_type      = $file->getMimeType();
        $media->aggregate_type = 'document';
        $media->size           = $file->getSize();
        $media->story_id       = $story->id;
        $media->save();

        Storage::disk(self::STORY_MEDIA_DISK)->putFileAs(
            self::STORY_MEDIA_DIRECTORY,
            $file,
            $filename . '.' . $extension,
            'public'
        );

        $media->countCharacters();

        return $media;
    }

    /**
     * Count the characters of the text the PDF holds and store them. Runs of
     * whitespace count as a single space, so the layout of the page does not
     * weigh in. A PDF that cannot be read — or a scan, which holds no text —
     * is left uncounted.
     */
    public function countCharacters(): ?int
    {
        try {
            $text = (new Parser)->parseFile(Storage::disk($this->disk)->path($this->relativePath()))->getText();
            $characters = mb_strlen(trim(preg_replace('/\s+/u', ' ', $text)));
        } catch (Throwable) {
            $characters = null;
        }

        $this->characters = $characters ?: null;
        $this->save();

        return $this->characters;
    }

    /**
     * The relative path of the stored file on its disk.
     */
    public function relativePath(): string
    {
        return $this->directory . '/' . $this->filename . '.' . $this->extension;
    }

    /**
     * The story attached to a media.
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }
}
