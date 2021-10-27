<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Laravel\Sanctum\Contracts\HasAbilities;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\Sanctum;

trait HasApiTokens
{
    /**
     * The access token the user is using for the current request.
     *
     * @var HasAbilities
     */
    protected $accessToken;

    /**
     * Get the access tokens that belong to model.
     *
     * @return MorphMany
     */
    public function tokens() : MorphMany {
        return $this->morphMany(Sanctum::$personalAccessTokenModel, 'tokenable', 'tokenable_type', 'tokenable_uuid', 'uuid');
    }

    /**
     * Determine if the current API token has a given scope.
     *
     * @param  string  $ability
     * @return bool
     */
    public function tokenCan(string $ability)
    {
        return $this->accessToken && $this->accessToken->can($ability);
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name
     * @param  array  $abilities
     * @return NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*'])
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    /**
     * Get the access token currently associated with the user.
     *
     * @return HasAbilities
     */
    public function currentAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the current access token for the user.
     *
     * @param  HasAbilities  $accessToken
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}
