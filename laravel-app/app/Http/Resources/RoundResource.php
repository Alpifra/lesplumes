<?php

namespace App\Http\Resources;

use App\Models\Round;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Round
 */
class RoundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'master'       => new UserResource($this->master),
            'participants' => new UserCollection($this->participants),
            'stories'      => new StoryCollection($this->roundStories),
            'jokers'       => $this->participants
                ->filter(fn ($plume) => $plume->pivot->joker_at)
                ->pluck('id')
                ->values(),
            'jokers_left'  => $this->when(
                $request->routeIs('api.rounds.show', 'api.rounds.joker.*'),
                fn () => $request->user()?->jokersLeft()
            ),
            'word'         => $this->word,
            'status'       => $this->status,
            'start_at'     => $this->start_at,
            'end_at'       => $this->end_at,
            'created_at'   => $this->created_at,
        ];
    }
}
