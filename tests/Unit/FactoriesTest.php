<?php

namespace Tests\Unit;

use App\Models\Device;
use App\Models\Geofence;
use App\Models\Location;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class FactoriesTest extends TestCase
{
    use DatabaseMigrations;

    public function test_model_factories()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $device = Device::factory()->create();

        $this->assertDatabaseHas('devices', [
            'uuid' => $device->uuid,
            'name' => $device->name,
        ]);

        $device->user()->associate($user)->save();

        $this->assertDatabaseHas('devices', [
            'uuid' => $device->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $this->assertEquals(1, $user->devices()->count());
        $this->assertEquals($device->uuid, $user->devices()->first()->uuid);

        $device->user()->dissociate()->save();

        $this->assertDatabaseMissing('devices', [
            'uuid' => $device->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $user->devices()->save($device);

        $this->assertDatabaseHas('devices', [
            'uuid' => $device->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $geofence = Geofence::factory()->create();

        $this->assertDatabaseHas('geofences', [
            'uuid' => $geofence->uuid,
            'name' => $geofence->name,
        ]);

        $geofence->devices()->attach($device);

        $this->assertDatabaseHas('device_geofence', [
            'device_uuid' => $device->uuid,
            'geofence_uuid' => $geofence->uuid,
        ]);

        $geofence->devices()->detach($device);

        $this->assertDatabaseMissing('device_geofence', [
            'device_uuid' => $device->uuid,
            'geofence_uuid' => $geofence->uuid,
        ]);

        $device->geofences()->attach($geofence);

        $this->assertDatabaseHas('device_geofence', [
            'device_uuid' => $device->uuid,
            'geofence_uuid' => $geofence->uuid,
        ]);

        $device->geofences()->detach($geofence);

        $this->assertDatabaseMissing('device_geofence', [
            'device_uuid' => $device->uuid,
            'geofence_uuid' => $geofence->uuid,
        ]);

        $location = Location::factory()->create();

        $this->assertDatabaseHas('locations', [
            'uuid' => $location->uuid,
            'is_moving' => $location->is_moving,
        ]);

        $device->locations()->save($location);

        $this->assertDatabaseHas('locations', [
            'uuid' => $location->uuid,
            'device_uuid' => $device->uuid,
        ]);

        $location->device()->disassociate()->save();

        $this->assertDatabaseMissing('locations', [
            'uuid' => $location->uuid,
            'device_uuid' => $device->uuid,
        ]);

        $location->geofence()->associate($geofence)->save();

        $this->assertDatabaseHas('locations', [
            'uuid' => $location->uuid,
            'geofence_uuid' => $geofence->uuid,
        ]);

        $this->assertEquals(1, $geofence->locations()->count());

        $personal_access_token = PersonalAccessToken::factory()->create();

        $personal_access_token->tokenable()->associate($device)->save();

        $this->assertDatabaseHas('personal_access_tokens', [
            'uuid' => $personal_access_token->uuid,
            'tokenable_type' => get_class($device),
            'tokenable_uuid' => $device->uuid,
        ]);
    }
}
