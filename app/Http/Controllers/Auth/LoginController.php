<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ToadAuthService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Auth\ToadUser;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';
    protected $toadAuth;

    public function __construct(ToadAuthService $toadAuth)
    {
        $this->middleware('guest')->except('logout');
        $this->toadAuth = $toadAuth;
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        $resp = $this->toadAuth->verify(
            $request->input('username'),
            $request->input('password')
        );

        if (!$resp) {
            throw ValidationException::withMessages([
                'username' => [trans('auth.failed')],
            ]);
        }

        $username = $request->input('username');

        $userData = [
            'id'             => $username,
            'email'          => null,
            'name'           => $username,
            'token'          => $resp['token'] ?? null,  // token JWT stocké en session, réutilisé par tous les services Toad
            'staff'          => ['username' => $username],
            'remember_token' => null,
        ];

        $request->session()->put('toad_user', $userData);

        // remember me à false - ToadUserProvider ne supporte pas les tokens persistants
        $user = new ToadUser($userData);
        Auth::login($user, false);

        return $this->sendLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        // "username" et non "email" pour coller au champ login du staff dans Toad
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    }
}
