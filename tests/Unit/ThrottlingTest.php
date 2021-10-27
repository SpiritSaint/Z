<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ThrottlingTest extends TestCase
{
    use DatabaseMigrations;

    public function test_throttling()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-users']
        );
        for ($i = 0; $i < 5; $i++) {
            $response = $this->json('GET', '/api/user');
//            $response->dump();
        }
        $response = $this->json('GET', '/api/user');
        $response->assertStatus(429);
    }
}
