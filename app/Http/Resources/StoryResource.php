<?php

namespace App\Http\Resources;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Story
 */
class StoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'writer'     => new UserResource($this->writer),
            'media'      => new MediaResource($this->media),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
