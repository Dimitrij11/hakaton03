<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\ProfessorData;
use App\Models\StudentData;
use App\Models\Role;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function dashboard()
    {
        $stats = [
            'users_count' => User::count(),
            'students_count' => StudentData::count(),
            'professors_count' => ProfessorData::count(),
            'courses_count' => Course::count(),
            'categories_count' => Category::count(),
        ];

        return response()->json($stats);
    }

    public function getUsers()
    {
        $users = User::with('role')->paginate(10);
        return response()->json($users);
    }

    public function getUserById($id)
    {
        $user = User::with(['role', 'studentData', 'professorData'])->findOrFail($id);
        return response()->json($user);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'role_id' => 'sometimes|exists:roles,id',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
