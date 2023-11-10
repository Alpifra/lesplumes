<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Story;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
        'password'   => 'hashed',
    ];

    /**
     * The rounds where user is attached as master.
     */
    public function masterRounds(): HasMany
    {
        return $this->hasMany(related: Round::class, foreignKey: 'master_id', localKey: 'id');
    }

    /**
     * The stories where user is attached as writer.
     */
    public function writerStories(): HasMany
    {
        return $this->hasMany(related: Story::class, foreignKey: 'writer_id', localKey: 'id');
    }

    /**
     * The rounds where user is attached as participant.
     */
    public function participantRounds(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
