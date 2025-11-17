<?php

namespace App\Http\Controllers;

use App\Contracts\UserServiceInterface;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(protected UserServiceInterface $userService)
    {
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->allUsers();

        return UserResource::collection($users)->response();
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->create($request->validated());

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->userService->userById((int) $id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorize('view', $user);

        return (new UserResource($user))->response();
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = $this->userService->userById((int) $id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorize('update', $user);

        $updated_user = $this->userService->update((int) $id, $request->validated());

        return (new UserResource($updated_user))->response();
    }

    public function destroy(string $id): JsonResponse
    {
        $user = $this->userService->userById((int) $id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorize('delete', $user);

        $this->userService->delete((int) $id);

        return response()->json(null, 204);
    }
}
