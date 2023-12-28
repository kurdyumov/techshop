<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SqlService;
use Illuminate\Support\Facades\Auth;
use App\Guards\UserGuard;
// use App\Http\Requests\GuestRequest;
use App\Events\ReceiveOrder;

class LoginController extends Controller
{
    protected SqlService $sqlService;

    public function __construct() {
        $this->sqlService = new SqlService();
    }

    public function login(Request $request) {
        $credentials = [
            'login' => $request->input('login'),
            'password' => $request->input('password')
        ];
        $login = Auth::guard()->attempt($credentials);
        if ($login) {
            
            if ($this->sqlService->getCurrentOrderIfExists()->exists())
                event(new ReceiveOrder());
            return redirect()->route('page.profile');
        }
        return redirect()->back();
    }
}
