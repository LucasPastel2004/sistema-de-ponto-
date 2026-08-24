<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('it rejects unauthenticated requests', function () {
    $response = $this->getJson('/api/user');
    $response->assertStatus(401);
});

test('it accepts valid sanctum token', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJson(['id' => $user->id]);
});

test('it applies rate limiting', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    for ($i = 0; $i < 60; $i++) {
        $response = $this->getJson('/api/user');
        if ($response->status() === 429) {
            break;
        }
    }

    $response = $this->getJson('/api/user');
    $response->assertStatus(429);
});
