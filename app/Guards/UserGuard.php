<?php
namespace App\Guards;
use App\Models\User;
// use App\Services\SqlService;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\UserProvider;
use App\Providers\UserProvider as CUP;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Timebox;
use Symfony\Component\HttpFoundation\Request;
use App\Events\ReceiveOrder;


class UserGuard extends SessionGuard {

	public function __construct($name, UserProvider $provider, Session $session, Request $request = null, Timebox $timebox = null)
    {
    	// dd(3);
        parent::__construct($name, $provider, $session, $request, $timebox);
    }

	public function attempt(array $credentials = [], $remember = false) {
		if (!array_key_exists('login', $credentials) || !array_key_exists('password', $credentials))
			return false;
		$user = $this->provider->retrieveById($credentials['login']);
		$validate = $this->provider->validate(new User($user), $credentials['password']);
		if ($validate) 
			$this->loginUpd($user);
		return $validate;
	}

	public function login(AuthenticatableContract $user, $remember = false) {
		return;
	}

	public function loginUpd($user) {
		$this->session->put('user', new User($user));
	}

	public function logout() {
		// $this->user = null;
		$this->session->remove('user');
	}

	public function guest() {
		// return is_null($this->user());
		return is_null($this->session->get('user'));
	}

	public function user() {
		// return $this->user;
		return $this->session->get('user');
	}

	public function check() {
		return !$this->guest();
	}
}