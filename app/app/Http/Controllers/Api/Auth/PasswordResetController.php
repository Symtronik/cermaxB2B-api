<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
   

    /**
     * Faktyczna zmiana hasła (API)
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'redirect' => config('app.frontend_url'),
            'message' => __('passwords.reset'),
        ]);
    }
}
