<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Verification;
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
            'email' => 'required|email|max:50',
            'password' => 'required|max:50|min:8',
            'confirm_password' => 'required|max:50|min:8|same:password',
        ]);

        // Check if email already exists
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            // If user is already active, reject
            if ($existingUser->status === 'active') {
                return back()->withErrors(['email' => 'Email sudah terdaftar.'])->withInput();
            }

            // User is inactive/verify - check if OTP is still active
            $activeOtp = Verification::where('user_id', $existingUser->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->first();

            if ($activeOtp) {
                // OTP still active, reject registration with same message
                return back()->withErrors(['email' => 'Email sudah terdaftar.'])->withInput();
            }

            // OTP expired or no active OTP, delete old user and continue
            $existingUser->delete();
        }

        $request['status'] = 'verify';
        $user = User::create($request->all());
        Auth::login($user);
        return redirect('/verify');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
