<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\OwnerRole;
use Laravel\Jetstream\Role;
use Laravel\Sanctum\HasApiTokens;

trait HasTeams
{
    /**
     * Determine if the given team is the current team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function isCurrentTeam($team) : bool
    {
        return $team->uuid === $this->currentTeam->uuid;
    }

    /**
     * Get the current team of the user's context.
     *
     * @return BelongsTo
     */
    public function currentTeam() : BelongsTo
    {
        if (is_null($this->current_team_uuid) && $this->uuid) {
            $this->switchTeam($this->personalTeam());
        }

        return $this->belongsTo(Jetstream::teamModel(), 'current_team_uuid');
    }

    /**
     * Switch the user's context to the given team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function switchTeam($team) : bool
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $this->forceFill([
            'current_team_uuid' => $team->uuid,
        ])->save();

        $this->setRelation('currentTeam', $team);

        return true;
    }

    /**
     * Get all the teams the user owns or belongs to.
     *
     * @return Collection
     */
    public function allTeams() : Collection
    {
        return $this->ownedTeams->merge($this->teams)->sortBy('name');
    }

    /**
     * Get all of the teams the user owns.
     *
     * @return HasMany
     */
    public function ownedTeams() : HasMany
    {
        return $this->hasMany(Jetstream::teamModel());
    }

    /**
     * Get all of the teams the user belongs to.
     *
     * @return BelongsToMany
     */
    public function teams() : BelongsToMany
    {
        return $this->belongsToMany(Jetstream::teamModel(), Jetstream::membershipModel())
            ->withPivot('role')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Get the user's "personal" team.
     *
     * @return Team
     */
    public function personalTeam() : Team
    {
        return $this->ownedTeams->where('personal_team', true)->first();
    }

    /**
     * Determine if the user owns the given team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function ownsTeam($team) : bool
    {
        if (is_null($team)) {
            return false;
        }

        return $this->uuid == $team->{$this->getForeignKey()};
    }

    /**
     * Determine if the user belongs to the given team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function belongsToTeam($team) : bool
    {
        return $this->teams->contains(function ($t) use ($team) {
                return $t->uuid === $team->uuid;
            }) || $this->ownsTeam($team);
    }

    /**
     * Get the role that the user has on the team.
     *
     * @param  mixed  $team
     * @return Role|void
     */
    public function teamRole($team)
    {
        if ($this->ownsTeam($team)) {
            return new OwnerRole;
        }

        if (! $this->belongsToTeam($team)) {
            return;
        }

        return Jetstream::findRole($team->users->where(
            'uuid', $this->uuid
        )->first()->membership->role);
    }

    /**
     * Determine if the user has the given role on the given team.
     *
     * @param  mixed  $team
     * @param  string  $role
     * @return bool
     */
    public function hasTeamRole($team, string $role) : bool
    {
        if ($this->ownsTeam($team)) {
            return true;
        }

        return $this->belongsToTeam($team) && optional(Jetstream::findRole($team->users->where(
                'uuid', $this->uuid
            )->first()->membership->role))->key === $role;
    }

    /**
     * Get the user's permissions for the given team.
     *
     * @param  mixed  $team
     * @return array
     */
    public function teamPermissions($team) : array
    {
        if ($this->ownsTeam($team)) {
            return ['*'];
        }

        if (! $this->belongsToTeam($team)) {
            return [];
        }

        return $this->teamRole($team)->permissions;
    }

    /**
     * Determine if the user has the given permission on the given team.
     *
     * @param  mixed  $team
     * @param  string  $permission
     * @return bool
     */
    public function hasTeamPermission($team, string $permission) : bool
    {
        if ($this->ownsTeam($team)) {
            return true;
        }

        if (! $this->belongsToTeam($team)) {
            return false;
        }

        if (in_array(HasApiTokens::class, class_uses_recursive($this)) &&
            ! $this->tokenCan($permission) &&
            $this->currentAccessToken() !== null) {
            return false;
        }

        $permissions = $this->teamPermissions($team);

        return in_array($permission, $permissions) ||
            in_array('*', $permissions) ||
            (Str::endsWith($permission, ':create') && in_array('*:create', $permissions)) ||
            (Str::endsWith($permission, ':update') && in_array('*:update', $permissions));
    }
}
