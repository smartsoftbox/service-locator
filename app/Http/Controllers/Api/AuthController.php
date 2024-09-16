<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 *
 * API endpoints for user authentication, including login and logout.
 */
class AuthController extends Controller
{
    /**
     * User Login
     *
     * Authenticate a user and return a token.
     *
     * @bodyParam email string required The email address of the user. Example: user@example.com
     * @bodyParam password string required The password of the user. Example: password123
     * @response 200 {
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxIiwibmFtZSI6IkpvbmUgRG9lIiwiaWF0IjoxNTYwNzQ1OTY4LCJleHBpcmF0aW9uIjoiZG9lIiwiaWF0IjoxNTYwNzQ1OTY4fQ.qE0MFGm8er5mDdf4JqY9s8xlL78n3b7OfZQG-VZDaRQ",
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }

    /**
     * User Logout
     *
     * Invalidate the user's token and log out.
     *
     * @response 204 {
     *   "description": "No content. The user has been successfully logged out."
     * }
     * @response 401 {
     *   "message": "Unauthorized. No valid token provided."
     * }
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
