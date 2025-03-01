<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    private $usersFile = 'app/data/users.json';

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $users = json_decode(file_get_contents(storage_path($this->usersFile)), true);

        foreach ($users as $user) {
            if ($user['email'] === $request->email) {
                return response()->json(['message' => 'Email already exists'], 400);
            }
        }

        $newUser = [
            'id' => count($users) + 1,
            'name' => $request->name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'role_id' => 2,  // Default role
            'profile_picture' => null,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];

        $users[] = $newUser;
        file_put_contents(storage_path($this->usersFile), json_encode($users, JSON_PRETTY_PRINT));

        Session::put('user', $newUser);

        return response()->json(['message' => 'Registered successfully', 'user' => $newUser]);
    }

    // Login user by checking JSON file
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Load users
        $users = json_decode(file_get_contents(storage_path($this->usersFile)), true);

        // Find user by email
        $user = collect($users)->firstWhere('email', $credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user['password_hash'])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Store user in session
        Session::put('user', $user);

        return response()->json(['message' => 'Logged in successfully', 'user' => $user]);
    }

    // Logout user (clear session)
    public function logout(Request $request)
    {
        Session::forget('user');

        return response()->json(['message' => 'Logged out successfully']);
    }

    // Get current logged-in user
    public function user()
    {
        $user = Session::get('user');

        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        return response()->json(['user' => $user]);
    }
}
