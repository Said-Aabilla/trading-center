<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Enregistre un nouvel utilisateur.
     *
     * @param array $userData
     * @return User
     * @throws ValidationException
     * @throws \Exception
     */
    public function registerUser(array $userData): User
    {
        // Hasher le mot de passe
        $userData['password'] = Hash::make($userData['password']);

        // Créer l’utilisateur
        return User::create($userData);
    }

    /**
     * Connecte un utilisateur et renvoie un tableau contenant
     * l'utilisateur, le token, l'expiration, etc.
     *
     * @param array $credentials
     * @return array
     * @throws \Exception
     */
    public function loginUser(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Invalid credentials.');
        }

        $expiration = Carbon::now()->addHour();
        $token = $user->createToken('auth_token', ['*'], $expiration)->plainTextToken;

        return [
            'user'        => $user,
            'token'       => $token,
            'expires_at'  => $expiration->toDateTimeString()
        ];
    }

    /**
     * Révoque le token courant de l’utilisateur pour la déconnexion.
     *
     * @param User $user
     * @return void
     */
    public function logoutUser(User $user): void
    {
        /** 
         * @var \Laravel\Sanctum\PersonalAccessToken $token 
         */             
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }
    }
}
