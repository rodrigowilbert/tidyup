<?php

namespace Tests\Unit\Services;

use App\Contracts\UserRepositoryInterface;
use App\Contracts\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    protected MockInterface $userRepositoryMock;
    protected UserServiceInterface $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepositoryMock);
        $this->userService = $this->app->make(UserServiceInterface::class);
    }

    public function test_it_can_create_a_user_with_hashed_password(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plain-password',
        ];

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($argument) use ($data) {
                // Verifica se a senha foi hasheada
                return Hash::check($data['password'], $argument['password'])
                    && $argument['name'] === $data['name']
                    && $argument['email'] === $data['email'];
            }))
            ->andReturn(new User($data));

        $user = $this->userService->create($data);

        $this->assertInstanceOf(User::class, $user);
    }

    public function test_it_can_update_a_user(): void
    {
        $user = new User(['id' => 1, 'name' => 'Old Name']);
        $new_data = ['name' => 'New Name'];

        $this->userRepositoryMock
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($user);

        $this->userRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(1, $new_data)
            ->andReturn(true);
        
        $this->userRepositoryMock
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn(new User(array_merge($user->toArray(), $new_data)));

        $updated_user = $this->userService->update(1, $new_data);

        $this->assertEquals('New Name', $updated_user->name);
    }

    public function test_it_can_delete_a_user(): void
    {
        $this->userRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->userService->delete(1);

        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
