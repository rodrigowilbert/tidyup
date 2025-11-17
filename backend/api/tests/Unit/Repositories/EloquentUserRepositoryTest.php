<?php

namespace Tests\Unit\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentUserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(UserRepositoryInterface::class);
    }

    public function test_it_can_create_a_user(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $user = $this->repository->create($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_it_can_find_a_user_by_id(): void
    {
        $created_user = User::factory()->create();

        $found_user = $this->repository->find($created_user->id);

        $this->assertInstanceOf(User::class, $found_user);
        $this->assertEquals($created_user->id, $found_user->id);
    }

    public function test_it_can_update_a_user(): void
    {
        $user = User::factory()->create();
        $new_data = ['name' => 'Updated Name'];

        $result = $this->repository->update($user->id, $new_data);

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_it_can_delete_a_user(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->delete($user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_it_can_find_a_user_by_email(): void
    {
        $created_user = User::factory()->create(['email' => 'findme@example.com']);

        $found_user = $this->repository->findByEmail('findme@example.com');

        $this->assertInstanceOf(User::class, $found_user);
        $this->assertEquals($created_user->id, $found_user->id);
    }
}
