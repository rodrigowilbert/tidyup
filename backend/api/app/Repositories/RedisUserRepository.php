<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class RedisUserRepository implements UserRepositoryInterface
{
    protected UserRepositoryInterface $repository;
    protected int $cacheTime = 60;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(): Collection
    {
        return Cache::remember('users.all', $this->cacheTime, function () {
            return $this->repository->all();
        });
    }

    public function find(int $id): ?User
    {
        return Cache::remember("users.{$id}", $this->cacheTime, function () use ($id) {
            return $this->repository->find($id);
        });
    }

    public function findByEmail(string $email): ?User
    {
        $cache_key = 'user.email.' . hash('sha256', $email);

        $user_id = Cache::remember($cache_key, $this->cacheTime, function () use ($email) {
            $user = $this->repository->findByEmail($email);
            return $user ? $user->id : null;
        });

        if ($user_id) {
            return $this->find($user_id);
        }

        return null;
    }

    public function create(array $data): User
    {
        $user = $this->repository->create($data);
        Cache::forget('users.all');
        return $user;
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->find($id);
        
        $updated = $this->repository->update($id, $data);

        if ($updated) {
            Cache::forget("users.{$id}");
            Cache::forget('users.all');
            
            if ($user) {
                Cache::forget('user.email.' . hash('sha256', $user->email));
            }
            if (isset($data['email'])) {
                 Cache::forget('user.email.' . hash('sha256', $data['email']));
            }
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $user = $this->find($id);
        $deleted = $this->repository->delete($id);

        if ($deleted && $user) {
            Cache::forget("users.{$id}");
            Cache::forget('users.all');
            Cache::forget('user.email.' . hash('sha256', $user->email));
        }

        return $deleted;
    }
}
