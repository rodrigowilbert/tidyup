<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(protected User $model)
    {
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->find($id);

        if ($user) {
            return $user->update($data);
        }

        return false;
    }

    public function delete(int $id): bool
    {
        $user = $this->find($id);

        if ($user) {
            return $user->delete();
        }
        
        return false;
    }
}
