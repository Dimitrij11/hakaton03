<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\StudentData;
use App\Models\ProfessorData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        $user = User::find(Auth::id());

        if (!$user) {
            return $this->sendError('Unauthorized.', [], 401);
        }

        // Load related data based on user role
        if ($user->role === 'student') {
            $user->load('studentData', 'achievements', 'wishlistCourses');
        } elseif ($user->role === 'professor') {
            $user->load('professorData', 'professorData.courses');
        }

        return $this->sendResponse($user, 'User profile retrieved successfully.');
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());

        if (!$user) {
            return $this->sendError('Unauthorized.', [], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'profile_picture' => 'sometimes|string',
            // Profile-specific fields based on role
            'bio' => 'sometimes|string',
            'date_of_birth' => 'sometimes|date',
            'education_level' => 'sometimes|string',
            'specialization' => 'sometimes|string',
            'education' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        // Update user fields
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('profile_picture')) {
            $user->profile_picture = $request->profile_picture;
        }

        $user->save();

        // Update role-specific data
        if ($user->role === 'student' && $user->studentData) {
            $studentData = $user->studentData;

            if ($request->has('bio')) {
                $studentData->bio = $request->bio;
            }

            if ($request->has('date_of_birth')) {
                $studentData->date_of_birth = $request->date_of_birth;
            }

            if ($request->has('education_level')) {
                $studentData->education_level = $request->education_level;
            }

            $studentData->save();
        } elseif ($user->role === 'professor' && $user->professorData) {
            $professorData = $user->professorData;

            if ($request->has('bio')) {
                $professorData->bio = $request->bio;
            }

            if ($request->has('specialization')) {
                $professorData->specialization = $request->specialization;
            }

            if ($request->has('education')) {
                $professorData->education = $request->education;
            }

            $professorData->save();
        }

        // Reload user with updated data
        $user = User::find(Auth::id());

        if ($user->role === 'student') {
            $user->load('studentData');
        } elseif ($user->role === 'professor') {
            $user->load('professorData');
        }

        return $this->sendResponse($user, 'Profile updated successfully.');
    }

    /**
     * Get all users (admin only).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::with(['studentData', 'professorData'])->get();

        return $this->sendResponse($users, 'Users retrieved successfully.');
    }

    /**
     * Update a specific user (admin only).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role' => 'sometimes|in:admin,professor,student',
            'profile_picture' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $user->update($request->all());

        return $this->sendResponse($user, 'User updated successfully.');
    }

    /**
     * Delete a specific user (admin only).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        $user->delete();

        return $this->sendResponse([], 'User deleted successfully.');
    }
}
