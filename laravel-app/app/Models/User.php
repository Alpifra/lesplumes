<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'user_name',
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
     * Validate an incoming request
     */
    public static function validate(Request $request, User $user): Request
    {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',
            'user_name'  => "required|string|unique:users,user_name,{$user->id}|max:50",
        ]);

        return $request;
    }

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
