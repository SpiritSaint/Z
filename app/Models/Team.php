<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Jetstream;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @return void
     */
    protected static function boot() {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Str::uuid());
        });
    }

    /**
     * Get the owner of the team.
     *
     * @return BelongsTo
     */
    public function owner() : BelongsTo {
        return $this->belongsTo(Jetstream::userModel(), 'user_uuid', 'uuid');
    }

    /**
     * Get all the team's users including its owner.
     *
     * @return Collection
     */
    public function allUsers() : Collection {
        return $this->users->merge([$this->owner]);
    }

    /**
     * Get all the users that belong to the team.
     *
     * @return BelongsToMany
     */
    public function users() : BelongsToMany {
        return $this->belongsToMany(Jetstream::userModel(), Jetstream::membershipModel(), 'team_uuid', 'user_uuid', 'uuid', 'uuid')
            ->withPivot('role')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Determine if the given user belongs to the team.
     *
     * @param User $user
     * @return bool
     */
    public function hasUser($user) : bool {
        return $this->users->contains($user) || $user->ownsTeam($this);
    }

    /**
     * Determine if the given email address belongs to a user on the team.
     *
     * @param  string  $email
     * @return bool
     */
    public function hasUserWithEmail(string $email) : bool {
        return $this->allUsers()->contains(function ($user) use ($email) {
            return $user->email === $email;
        });
    }

    /**
     * Determine if the given user has the given permission on the team.
     *
     * @param User $user
     * @param string $permission
     * @return bool
     */
    public function userHasPermission(User $user, string $permission) : bool {
        return $user->hasTeamPermission($this, $permission);
    }

    /**
     * Get all the pending user invitations for the team.
     *
     * @return HasMany
     */
    public function teamInvitations() : HasMany {
        return $this->hasMany(Jetstream::teamInvitationModel(), 'team_uuid', 'uuid');
    }

    /**
     * Remove the given user from the team.
     *
     * @param User $user
     * @return void
     */
    public function removeUser(User $user) : void {
        if ($user->current_team_uuid === $this->uuid) {
            $user->forceFill([
                'current_team_uuid' => null,
            ])->save();
        }

        $this->users()->detach($user);
    }

    /**
     * Purge all the team's resources.
     *
     * @return void
     */
    public function purge() : void {
        $this->owner()->where('current_team_uuid', $this->uuid)
            ->update(['current_team_uuid' => null]);

        $this->users()->where('current_team_uuid', $this->uuid)
            ->update(['current_team_uuid' => null]);

        $this->users()->detach();

        $this->delete();
    }
}
