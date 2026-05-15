<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

// provider minimaliste - l'authentification réelle est dans ToadAuthService, ici on reconstruit juste l'utilisateur depuis la session
class ToadUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        $data = session('toad_user');
        $id = $data['id'] ?? $data['email'] ?? null;

        if ($data && $id == $identifier) {
            return new ToadUser($data);
        }
        return null;
    }

    public function retrieveByToken($identifier, $token) { return null; }
    public function updateRememberToken(Authenticatable $user, $token) {}
    public function retrieveByCredentials(array $credentials) { return null; }
    public function validateCredentials(Authenticatable $user, array $credentials) { return false; }
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void {}
}
