<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Mail\OtpEmail;
use App\Models\Verification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    public function index()
    {
        return view('verification.index');
    }

    public function show($unique_id)
    {
        $verify = Verification::whereUserId(Auth::user()->id)->whereUniqueId($unique_id)
            ->whereStatus('active')->count();

        if (!$verify)
            abort(404);
        return view('verification.show', compact('unique_id'));
    }

    public function update(Request $request, $unique_id)
    {
        $verify = Verification::whereUserId(Auth::user()->id)->whereUniqueId($unique_id)
            ->whereStatus('active')->first();

        if (!$verify)
            abort(404);
        if (md5($request->otp) != $verify->otp) {
            $verify->update(['status' => 'invalid']);
            return redirect('/verify')->with('Failed', 'Kode OTP salah!');
        }
        $verify->update(['status' => 'valid']);
        User::find($verify->user_id)->ForceFill(['status' => 'active'])->save();
        Auth::logout();
        return redirect('/login')->with('Success', 'Email berhasil diverifikasi! Silakan login.');
    }

    public function store(Request $request)
    {
        $user = null;

        if ($request->type == 'register' || $request->type == 'resend') {
            $user = User::find($request->user()->id);
        } else {
            // $user = reset password logic
        }

        if (!$user) {
            return back()->with('Failed', 'User tidak ditemukan!');
        }

        // Invalidate previous OTP
        Verification::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'invalid']);

        $otp = rand(100000, 999999);
        $verify = Verification::create([
            'user_id' => $user->id,
            'unique_id' => uniqid(),
            'otp' => md5($otp),
            'type' => $request->type == 'resend' ? 'register' : $request->type,
            'send_via' => 'email',
        ]);

        try {
            Mail::to($user->email)->send(new OtpEmail($otp));
        } catch (\Exception $e) {
            return redirect('/verify/' . $verify->unique_id)->with('Failed', 'Gagal mengirim email: ' . $e->getMessage());
        }

        return redirect('/verify/' . $verify->unique_id)->with('Success', 'Kode OTP telah dikirim ke email Anda!');
    }
}
