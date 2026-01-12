<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:50',
            'password' => 'required|string|max:50',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return redirect('/dashboard');
        }

        return back()->with('Failed', 'Login gagal! Periksa kembali email dan password Anda.');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|min:3',
            'email' => 'required|email|max:50|unique:users,email',
            'password' => 'required|max:50|min:8',
            'confirm_password' => 'required|max:50|min:8|same:password',
        ]);

        $request['status'] = 'verify';
        $user = User::create($request->all());
        Auth::login($user);
        return redirect('/dashboard');
    }

    public function logout()
    {
        Auth::logout(Auth::user());
        return redirect('/login');
    }
}
