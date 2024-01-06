<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
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
            'url'        => "/demo",
            'extension'  => $this->extension,
            'mime_type'  => $this->mime_type,
            'size'       => $this->size,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
