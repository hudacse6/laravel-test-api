<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\InvitationLinkForSignUp;
use App\Notifications\SignUpPinSend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['signup', 'signupPin']);
    }

    public function invite(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $token = Str::random(20);

        $user = User::updateOrCreate([
            'email' => $request->email,
        ], [
            'email' => $request->email,
            'invitation_token' => $token
        ]);

        Notification::send($user, new InvitationLinkForSignUp($user));

        return response()->json([
            'success' => true,
            'message' => "Successfully send invitation link"
        ]);

    }

    public function signup(Request $request)
    {
        $token = $request->query('token');

        $request->validate([
            'username' => 'required|min:3|max:20',
            "password" => "required"
        ]);

        if ($token) {
            $user = User::where('invitation_token', $token)->first();
            if ($user) {
                $six_digit_random_number = random_int(100000, 999999);
                $user->username = $request->username;
                $user->password = Hash::make($request->password);
                $user->invitation_token = null;
                $user->pin = $six_digit_random_number;
                $user->save();

                Notification::send($user, new SignUpPinSend($user));

                return response()->json([
                    'success' => true,
                    'message' => "Successfully send 6 digit confirm pin to email!"
                ]);

            }
        }
        return response()->json([
            'success' => false,
            'message' => "Something wrong please try again!"
        ]);
    }


    public function signupPin(Request $request){
        $request->validate([
            'email' => 'required|email',
            "pin" => "required"
        ]);

        $user = User::where(['email' => $request->email, 'pin' => $request->pin])->first();
        if ($user){
            $user->pin = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => "Successfully signup complete please try login!"
            ]);

        }
        return response()->json([
            'success' => false,
            'message' => "Something wrong please try again!"
        ]);
    }
}
