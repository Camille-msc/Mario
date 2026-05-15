<?php

namespace App\Http\Controllers\Auth;

use App\Auth\ToadUser;
use App\Http\Controllers\Controller;
use App\Services\ToadStaffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'username'   => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $staffService = app(ToadStaffService::class);
        $result = $staffService->createStaff($request->all());

        if (!empty($result['_error'])) {
            throw ValidationException::withMessages([
                'email' => ['Erreur API (' . $result['status'] . ') : ' . $result['message']],
            ]);
        }

        $userData = [
            'id'    => $result['staffId'] ?? $result['id'] ?? $request->input('email'),
            'email' => $result['email'] ?? $request->input('email'),
            'name'  => trim(($result['firstName'] ?? $request->input('first_name')) . ' ' . ($result['lastName'] ?? $request->input('last_name'))),
            'token' => null,
            'staff' => $result,
        ];

        $request->session()->put('toad_user', $userData);

        $user = new ToadUser($userData);
        Auth::login($user, false);

        return redirect($this->redirectTo);
    }
}