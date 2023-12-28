<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\UserProvider as UP;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Services\SqlService;

class UserProvider extends ServiceProvider implements UP
{
    protected User $user;
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function retrieveById($login)
    {
        // dd(2);
        $user = $this->sqlService->retrieveUserByLogin($login)->first();
        return $user;
    }

    public function retrieveByToken($identifier, $token)
    {
        // TODO: Implement retrieveByToken() method.
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // TODO: Implement updateRememberToken() method.
    }

    public function retrieveByCredentials(array $credentials)
    {
        // TODO: Implement retrieveByCredentials() method.
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return;
    }

    public function validate(User $user, $password) {
        return $user->getMetadata()['user']->password == $password;
    }
}
