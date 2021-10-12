<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Geofence;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PersonalAccessTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PersonalAccessToken::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tokenable_type' => $this->faker->randomElement([
                Device::class,
                User::class,
            ]),
            'tokenable_uuid' => Str::uuid(),
            'name' => $this->faker->text(19),
            'token' => Str::random(64),
        ];
    }
}
