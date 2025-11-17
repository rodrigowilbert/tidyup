<?php

namespace Tests\Unit\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use App\Repositories\RedisUserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class RedisUserRepositoryTest extends TestCase
{
    protected MockInterface $mockEloquentRepository;
    protected RedisUserRepository $redisUserRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockEloquentRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->redisUserRepository = new RedisUserRepository($this->mockEloquentRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_all_users_are_cached(): void
    {
        $users = new Collection([new User(['id' => 1, 'name' => 'Test User'])]);

        Cache::shouldReceive('remember')
            ->once()
            ->with('users.all', Mockery::any(), Mockery::type(\Closure::class))
            ->andReturnUsing(function ($key, $ttl, $callback) use ($users) {
                return $callback();
            });

        $this->mockEloquentRepository->shouldReceive('all')
            ->once()
            ->andReturn($users);

        $result = $this->redisUserRepository->all();

        $this->assertEquals($users, $result);
    }

    public function test_user_by_id_is_cached(): void
    {
        $user = new User(['id' => 1, 'name' => 'Test User']);

        Cache::shouldReceive('remember')
            ->once()
            ->with('users.1', Mockery::any(), Mockery::type(\Closure::class))
            ->andReturn($user);

        $this->mockEloquentRepository->shouldNotReceive('find');

        $result = $this->redisUserRepository->find(1);

        $this->assertEquals($user, $result);
    }

    public function test_user_by_email_is_cached(): void
    {
        $email = 'test@example.com';
        $user_id = 1;
        $user = new User(['id' => $user_id, 'name' => 'Test User', 'email' => $email]);
        $cache_key = 'user.email.' . hash('sha256', $email);

        Cache::shouldReceive('remember')
            ->once()
            ->with($cache_key, Mockery::any(), Mockery::type(\Closure::class))
            ->andReturn($user_id);

        Cache::shouldReceive('remember')
            ->once()
            ->with('users.' . $user_id, Mockery::any(), Mockery::type(\Closure::class))
            ->andReturn($user);

        $this->mockEloquentRepository->shouldNotReceive('findByEmail');
        $this->mockEloquentRepository->shouldNotReceive('find');

        $result = $this->redisUserRepository->findByEmail($email);

        $this->assertEquals($user, $result);
    }

    public function test_create_invalidates_all_users_cache(): void
    {
        $user_data = ['name' => 'New User', 'email' => 'new@example.com', 'password' => 'password'];
        $user = new User($user_data);

        $this->mockEloquentRepository->shouldReceive('create')
            ->once()
            ->with($user_data)
            ->andReturn($user);

        Cache::shouldReceive('forget')
            ->once()
            ->with('users.all');

        $result = $this->redisUserRepository->create($user_data);

        $this->assertEquals($user, $result);
    }

    public function test_update_invalidates_relevant_caches(): void
    {
        $user_id = 1;
        $old_email = 'old@example.com';
        $new_email = 'new@example.com';
        $user = new User(['id' => $user_id, 'name' => 'Old Name', 'email' => $old_email]);
        $updated_data = ['name' => 'Updated Name', 'email' => $new_email];

        Cache::shouldReceive('remember')
            ->once()
            ->with('users.' . $user_id, Mockery::any(), Mockery::type(\Closure::class))
            ->andReturn($user);

        $this->mockEloquentRepository->shouldReceive('update')
            ->once()
            ->with($user_id, $updated_data)
            ->andReturn(true);

        Cache::shouldReceive('forget')
            ->once()
            ->with('users.' . $user_id);
        Cache::shouldReceive('forget')
            ->once()
            ->with('users.all');
        Cache::shouldReceive('forget')
            ->once()
            ->with('user.email.' . hash('sha256', $old_email));
        Cache::shouldReceive('forget')
            ->once()
            ->with('user.email.' . hash('sha256', $new_email));

        $result = $this->redisUserRepository->update($user_id, $updated_data);

        $this->assertTrue($result);
    }

    public function test_delete_invalidates_relevant_caches(): void
    {
        $user_id = 1;
        $email = 'test@example.com';
        $user = new User(['id' => $user_id, 'name' => 'Test User', 'email' => $email]);

        Cache::shouldReceive('remember')
            ->once()
            ->with('users.' . $user_id, Mockery::any(), Mockery::type(\Closure::class))
            ->andReturn($user);

        $this->mockEloquentRepository->shouldReceive('delete')
            ->once()
            ->with($user_id)
            ->andReturn(true);

        Cache::shouldReceive('forget')
            ->once()
            ->with('users.' . $user_id);
        Cache::shouldReceive('forget')
            ->once()
            ->with('users.all');
        Cache::shouldReceive('forget')
            ->once()
            ->with('user.email.' . hash('sha256', $email));

        $result = $this->redisUserRepository->delete($user_id);

        $this->assertTrue($result);
    }
}
