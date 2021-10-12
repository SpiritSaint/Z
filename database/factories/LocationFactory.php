<?php

namespace Database\Factories;

use App\Models\Geofence;
use App\Models\Location;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $speed = $this->faker->numberBetween(0, 10);

        return [
            'event' => $this->faker->randomElement([
                'motionchange', 'geofence', 'heartbeat', null
            ]),
            'speed' => $speed,
            'is_moving' => $speed > 0,
            'coords' => new Point($this->faker->latitude, $this->faker->longitude),
            'altitude' => $this->faker->randomNumber(),
            'accuracy' => $this->faker->numberBetween(0, 20),
            'heading' => $this->faker->numberBetween(0, 360),
            'activity_type' => $this->faker->randomElement([
                'still',
                'on_foot',
                'walking',
                'running',
                'in_vehicle',
                'on_bicycle',
            ]),
            'activity_confidence' => $this->faker->numberBetween(0, 100),
            'battery_level' => $this->faker->numberBetween(0, 100),
            'battery_is_charging' => $this->faker->boolean(),
            'odometer' => $this->faker->numberBetween(0, 500),
            'timestamp' => now(),
        ];
    }
}
