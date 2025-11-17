<?php

namespace App\Contracts;

use App\Models\User;

interface UserServiceInterface
{
    public function allUsers(): \Illuminate\Database\Eloquent\Collection;

    public function userById(int $id): ?User;

    public function create(array $data): User;

    public function update(int $id, array $data): ?User;

    public function delete(int $id): bool;
}
