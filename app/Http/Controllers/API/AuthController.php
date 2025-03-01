<?php

namespace app\Http\Controllers\API;

use App\Models\User;
use App\Models\StudentData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends BaseController
{
    /**
     * Register a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:admin,professor,student',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $role = $request->role ?? 'student';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        // If the user is a student, create student data
        if ($role === 'student') {
            StudentData::create([
                'user_id' => $user->id,
                'date_of_birth' => null,
                'education_level' => null,
                'bio' => null,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $result = [
            'user' => $user,
            'token' => $token,
        ];

        return $this->sendResponse($result, 'User registered successfully.');
    }

    /**
     * Login user and create token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->sendError('Unauthorized.', ['error' => 'Invalid credentials'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        $result = [
            'user' => $user,
            'token' => $token,
        ];

        return $this->sendResponse($result, 'User logged in successfully.');
    }

    /**
     * Logout user (Revoke Token).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'User logged out successfully.');
    }

    /**
     * Get the authenticated User.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return $this->sendResponse($request->user(), 'User retrieved successfully.');
    }

    /**
     * Redirect to Google OAuth.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'password' => Hash::make(rand(1, 10000)), // Random password for OAuth users
                    'role' => 'student', // Default role for Google OAuth users
                ]
            );

            // Create student data if it doesn't exist
            if ($user->role === 'student' && !$user->studentData) {
                StudentData::create([
                    'user_id' => $user->id,
                    'date_of_birth' => null,
                    'education_level' => null,
                    'bio' => null,
                ]);
            }

            $token = $user->createToken('google-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return $this->sendError('Google authentication failed.', ['error' => $e->getMessage()], 500);
        }
    }
}
