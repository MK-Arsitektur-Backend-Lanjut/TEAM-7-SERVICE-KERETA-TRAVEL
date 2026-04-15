<?php

namespace App\Http\Controllers;

use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }

    public function show(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:6'],
        ]);

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $updated = $this->users->update($user, $data);

        return response()->json([
            'user' => $updated,
        ]);
    }
}
