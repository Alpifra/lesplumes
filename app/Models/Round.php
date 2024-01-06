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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
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
        ]);

        return $request;
    }

    /**
     * The master attached to a round.
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, foreignKey: 'master_id', ownerKey: 'id');
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
