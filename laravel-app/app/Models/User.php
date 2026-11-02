<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Mail\ResetPassword;
use App\Models\Story;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

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
        'created_at'     => 'datetime',
        'deactivated_at' => 'datetime',
        'password'       => 'hashed',
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
     * Whether the plume left the circle.
     */
    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    /**
     * Take the plume out of the circle while keeping her history.
     *
     * She can no longer log in and leaves the rotation and the lists, but her
     * texts and the sessions she took part in stay as they were. A browser
     * session still open is turned away by the EnsureUserIsActive middleware.
     */
    public function deactivate(): void
    {
        $this->deactivated_at = now();
        $this->save();
    }

    /**
     * Bring a deactivated plume back into the circle.
     */
    public function reactivate(): void
    {
        $this->deactivated_at = null;
        $this->save();
    }

    /**
     * Only the plumes still in the circle.
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('deactivated_at');
    }

    /**
     * The circle in rotation order: alphabetical by first name, then last
     * name, the id settling namesakes so the order never wavers.
     */
    public function scopeInCircleOrder(Builder $query): void
    {
        $query->orderBy('first_name')->orderBy('last_name')->orderBy('id');
    }

    /**
     * The plume who follows the given one in the circle, starting over at
     * the first plume after the last.
     *
     * The comparison is left to the database so it follows the same
     * collation as the order — accents and case included. The given plume
     * may have left the circle: the turn still resumes from her place.
     */
    public static function nextInCircle(User $plume): ?self
    {
        return static::active()
            ->whereRaw('(first_name, last_name, id) > (?, ?, ?)', [$plume->first_name, $plume->last_name, $plume->id])
            ->inCircleOrder()
            ->first()
            ?? static::active()->inCircleOrder()->first();
    }

    /**
     * Jokers the plume may still play this calendar year.
     *
     * A joker is counted in the year it was played, so the quota starts
     * afresh every 1st of January.
     */
    /**
     * Mail the plume the link to choose a new password, in the style of the
     * circle rather than Laravel's default notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        Mail::to($this->email)->send(new ResetPassword($this, $token));
    }

    /**
     * Jokers the plume may still play this calendar year.
     */
    public function jokersLeft(): int
    {
        $played = $this->participantRounds()
            ->wherePivotBetween('joker_at', [now()->startOfYear(), now()->endOfYear()])
            ->count();

        return max(0, Round::JOKERS_PER_YEAR - $played);
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
        return $this->belongsToMany(Round::class)->withPivot('joker_at');
    }
}
