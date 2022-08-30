<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class NewPasswordController extends Controller
{
    public function forgotPassword(Request $request){
        $request->validate([
            'email'=>'required|email',
        ]);
        $status = Password::sendResetLink(
            $request->only('email')
        );
        if($status == Password::RESET_LINK_SENT){
            return [
                'status'=> __($status)
            ];
        }
        throw ValidationException::withMessages([
            'email'=> [trans($status)]
        ]);
    }

    public function reset(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
     
                $user->save();
                // $user()->tokens()->delete();
                event(new PasswordReset($user));
            }
        );
        if($status == Password::PASSWORD_RESET){
            return response([
                'message'=> 'Password reset successfully'
            ]);
        }
        return response([
            'message'=> ($status)
        ],500);
    }
}
