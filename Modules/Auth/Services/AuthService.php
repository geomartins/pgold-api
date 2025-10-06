<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;



class AuthService
{
    public function attemptLogin(array $credentials)
    {
        // Check user by email
        $user = User::where('email', $credentials['email'])->first();

        // Invalid credentials
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return [false, "Invalid credentials"];
        }

        // Create Sanctum token
        $token = $user->createToken('api_token')->plainTextToken;

        // Return both user and token
        return [true, $user, $token];
    }


    public function checkUsername(string $username): array
    {
        $exists = User::where('username', $username)->exists();

        if ($exists) {
            return [false, 'Username is already taken'];
        }

        return [
            true,
            'Username is available'
        ];
    }


    public function register(array $data): array
    {
        try {
            // Hash password before saving
            $data['password'] = Hash::make($data['password']);

            // Create user with fillable protection
            $user = User::create($data);

            // Generate Sanctum token
            $token = $user->createToken('api_token')->plainTextToken;

            return [ true, $user, $token];


        } catch (\Exception $e) {

            Log::error('Registration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [ false, 'Registration failed: ' . $e->getMessage()];
        }
    }

}
