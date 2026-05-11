<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('user.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            $user = User::create([
                'name' => strstr($credentials['email'], '@', true) ?: $credentials['email'],
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);
        } elseif (!Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Mật khẩu không đúng cho tài khoản đã tồn tại.',
            ])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login');
    }
}
