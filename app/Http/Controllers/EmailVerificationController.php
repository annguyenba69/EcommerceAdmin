<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request){
        if($request->user()->hasVerifiedEmail()){
            return response()->json([
                'status'=> 200,
                'message'=> 'Already Verified'
            ]);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json([
            'status'=> 200,
            'message'=> 'Sent Verification Link To Email'
        ]);
    }

    public function verify(EmailVerificationRequest $request){
        if($request->user()->hasVerifiedEmail()){
            return response()->json([
                'status'=> 200,
                'message'=> 'Already Verified'
            ]);
        }
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }
        return response()->json([
            'status'=> 200,
            'message'=> 'Email has been verified'
        ]);
    }

    public function resend(Request $request){
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    }
}
