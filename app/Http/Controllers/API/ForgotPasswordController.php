<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address format.',
            'email.exists' => 'The email address is not registered in our system.',
        ]);

        $user = User::where('email', $request->email)->first();
        $token = Str::random(60);

        // Store or update token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send Notification Email to User
        $user->notify(new ResetPasswordNotification($token));

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset instructions have been sent to your email.',
            'debug_token' => config('app.debug') ? $token : null 
        ], 200);
    }

    /**
     * Execute Password Reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Email address is required.',
            'email.exists' => 'Email address not found.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        // Fetch token record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired reset token.'
            ], 400);
        }

        // Check if token expired (> 60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired. Please request a new password reset.'
            ], 400);
        }

        // Update User Password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete used token from database
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Your password has been successfully reset. Please log in.'
        ], 200);
    }
}
