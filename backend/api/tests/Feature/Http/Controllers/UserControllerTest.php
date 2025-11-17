<?php

namespace Tests\Feature\Http\Controllers;

use App\Contracts\UserServiceInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected MockInterface $userServiceMock;
    protected User $actingUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userServiceMock = Mockery::mock(UserServiceInterface::class);
        $this->app->instance(UserServiceInterface::class, $this->userServiceMock);

        $this->actingUser = User::factory()->create();
        Sanctum::actingAs($this->actingUser);
    }

    public function test_index_returns_a_list_of_users(): void
    {
        $users = User::factory()->count(3)->make();

        $this->userServiceMock
            ->shouldReceive('allUsers')
            ->once()
            ->andReturn($users);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
                 ->assertJsonCount(count($users), 'data');
    }

    public function test_store_creates_a_new_user(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password'
        ];

        $this->userServiceMock
            ->shouldReceive('create')
            ->once()
            ->with($userData)
            ->andReturn(new User($userData));

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['email' => 'new@example.com']);
    }

    public function test_show_returns_a_specific_user(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $this->userServiceMock
            ->shouldReceive('userById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $response = $this->getJson('/api/v1/users/1');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => 1]);
    }

    public function test_show_returns_404_if_user_not_found(): void
    {
        $this->userServiceMock
            ->shouldReceive('userById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $response = $this->getJson('/api/v1/users/999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_an_existing_user(): void
    {
        $updateData = ['name' => 'Updated Name'];

        $userId = $this->actingUser->id;

        $this->userServiceMock
            ->shouldReceive('userById')
            ->once()
            ->with($userId)
            ->andReturn($this->actingUser);

        $this->userServiceMock
            ->shouldReceive('update')
            ->once()
            ->with($userId, $updateData)
            ->andReturn(tap($this->actingUser)->fill($updateData));

        $response = $this->putJson("/api/v1/users/{$userId}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_destroy_removes_a_user(): void
    {
        $userId = $this->actingUser->id;

        $this->userServiceMock
            ->shouldReceive('userById')
            ->once()
            ->with($userId)
            ->andReturn($this->actingUser);

        $this->userServiceMock
            ->shouldReceive('delete')
            ->once()
            ->with($userId)
            ->andReturn(true);

        $response = $this->deleteJson("/api/v1/users/{$userId}");

        $response->assertStatus(204);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
