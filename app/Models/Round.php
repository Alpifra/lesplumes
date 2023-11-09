<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
     * The master attached to a round.
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, foreignKey: 'master_id', ownerKey: 'id');
    }

    /**
     * Get the participants that belong to the round.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
