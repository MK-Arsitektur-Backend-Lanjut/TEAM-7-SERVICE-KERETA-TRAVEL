<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function update(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user->refresh();
    }
}
