<?php

namespace App\Services\V1\Auth;

// use App\Mail\V1\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class AuthService
{
    public function registerUser(string $name, string $email, string $password): array
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $token = (new TokenService)->createToken(user: $user);

        // Mail::to($user)->queue(new WelcomeMail($user));

        return [$user, $token];
    }

    public function loginUser(string $email, string $password): array
    {
        $user = User::where([
            'email' => $email,
        ])->firstOrFail();

        if (! Hash::check($password, $user->password)) {
            throw new InvalidArgumentException('Invalid credentials');
        }

        $token = (new TokenService)->createToken(user: $user);

        return [$user, $token];
    }
}
