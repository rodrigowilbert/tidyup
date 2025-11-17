<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Contracts\UserServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    public function __construct(protected UserRepositoryInterface $userRepository)
    {
    }

    public function allUsers(): Collection
    {
        return $this->userRepository->all();
    }

    public function userById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(array $data): User
    {
        return $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(int $id, array $data): ?User
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return null;
        }

        $this->userRepository->update($id, $data);

        return $this->userRepository->find($id);
    }

    public function delete(int $id): bool
    {
        return $this->userRepository->delete($id);
    }
}
