<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class LessonController extends BaseController
{
    /**
     * Get all lessons for a specific module.
     *
     * @param  int  $moduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByModule($moduleId)
    {
        $module = Module::find($moduleId);

        if (!$module) {
            return $this->sendError('Module not found.');
        }

        $course = Course::find($module->course_id);

        // Check if user is enrolled in the course or is the professor or admin
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->sendError('User not authenticated properly.');
        }

        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$isEnrolled && !$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id)) {
            return $this->sendError('Unauthorized.', ['error' => 'You must be enrolled in this course to view its lessons'], 403);
        }

        $lessons = Lesson::where('module_id', $moduleId)
            ->orderBy('order', 'asc')
            ->get();

        return $this->sendResponse($lessons, 'Lessons retrieved successfully.');
    }

    /**
     * Get a specific lesson.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $lesson = Lesson::find($id);

        if (!$lesson) {
            return $this->sendError('Lesson not found.');
        }

        $module = Module::find($lesson->module_id);
        $course = Course::find($module->course_id);

        // Check if user is enrolled in the course or is the professor or admin
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->sendError('User not authenticated properly.');
        }

        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$isEnrolled && !$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id)) {
            return $this->sendError('Unauthorized.', ['error' => 'You must be enrolled in this course to view this lesson'], 403);
        }

        return $this->sendResponse($lesson, 'Lesson retrieved successfully.');
    }

    /**
     * Store a newly created lesson.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'duration' => 'required|integer|min:1',
            'order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $module = Module::find($request->module_id);
        $course = Course::find($module->course_id);

        // Check if user is the professor of the course or admin
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->sendError('User not authenticated properly.');
        }

        if (!$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id)) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have permission to create lessons for this course'], 403);
        }

        $lesson = new Lesson();
        $lesson->module_id = $request->module_id;
        $lesson->title = $request->title;
        $lesson->content = $request->content;
        $lesson->video_url = $request->video_url;
        $lesson->duration = $request->duration;
        $lesson->order = $request->order;
        $lesson->save();

        return $this->sendResponse($lesson, 'Lesson created successfully.');
    }

    /**
     * Update the specified lesson.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'content' => 'string',
            'video_url' => 'nullable|url',
            'duration' => 'integer|min:1',
            'order' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $lesson = Lesson::find($id);

        if (!$lesson) {
            return $this->sendError('Lesson not found.');
        }

        $module = Module::find($lesson->module_id);
        $course = Course::find($module->course_id);

        // Check if user is the professor of the course or admin
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->sendError('User not authenticated properly.');
        }

        if (!$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id)) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have permission to update lessons for this course'], 403);
        }

        if (isset($request->title)) {
            $lesson->title = $request->title;
        }

        if (isset($request->content)) {
            $lesson->content = $request->content;
        }

        if (isset($request->video_url)) {
            $lesson->video_url = $request->video_url;
        }

        if (isset($request->duration)) {
            $lesson->duration = $request->duration;
        }

        if (isset($request->order)) {
            $lesson->order = $request->order;
        }

        $lesson->save();

        return $this->sendResponse($lesson, 'Lesson updated successfully.');
    }

    /**
     * Remove the specified lesson.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $lesson = Lesson::find($id);

        if (!$lesson) {
            return $this->sendError('Lesson not found.');
        }

        $module = Module::find($lesson->module_id);
        $course = Course::find($module->course_id);

        // Check if user is the professor of the course or admin
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->sendError('User not authenticated properly.');
        }

        if (!$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id)) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have permission to delete lessons for this course'], 403);
        }

        $lesson->delete();

        return $this->sendResponse([], 'Lesson deleted successfully.');
    }

    /**
     * Get all modules for a specific course.
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getModulesByCourse($courseId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            return $this->sendError('Course not found.');
        }

        // Check if user is enrolled in the course or is the professor or admin
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->sendError('User not authenticated properly.');
        }

        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();

        if (!$isEnrolled && !$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id)) {
            return $this->sendError('Unauthorized.', ['error' => 'You must be enrolled in this course to view its modules'], 403);
        }

        $modules = Module::where('course_id', $courseId)
            ->orderBy('order', 'asc')
            ->with(['lessons' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
            ->get();

        return $this->sendResponse($modules, 'Modules retrieved successfully.');
    }

    /**
     * Store a newly created module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeModule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $course = Course::find($request->course_id);

        // Check if user is the professor of the course or admin
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->sendError('User not authenticated properly.');
        }

        if (!$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id)) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have permission to create modules for this course'], 403);
        }

        $module = new Module();
        $module->course_id = $request->course_id;
        $module->title = $request->title;
        $module->description = $request->description;
        $module->order = $request->order;
        $module->save();

        return $this->sendResponse($module, 'Module created successfully.');
    }

    /**
     * Update the specified module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateModule(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $module = Module::find($id);

        if (!$module) {
            return $this->sendError('Module not found.');
        }

        $course = Course::find($module->course_id);

        // Check if user is the professor of the course or admin
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->sendError('User not authenticated properly.');
        }

        if (!$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id)) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have permission to update modules for this course'], 403);
        }

        if (isset($request->title)) {
            $module->title = $request->title;
        }

        if (isset($request->description)) {
            $module->description = $request->description;
        }

        if (isset($request->order)) {
            $module->order = $request->order;
        }

        $module->save();

        return $this->sendResponse($module, 'Module updated successfully.');
    }

    /**
     * Remove the specified module.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyModule($id)
    {
        $module = Module::find($id);

        if (!$module) {
            return $this->sendError('Module not found.');
        }

        $course = Course::find($module->course_id);

        // Check if user is the professor of the course or admin
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->sendError('User not authenticated properly.');
        }

        if (!$user->isAdmin() && !($user->isProfessor() && $course->professor_id == $user->id)) {
            return $this->sendError('Unauthorized.', ['error' => 'You do not have permission to delete modules for this course'], 403);
        }

        // Check if module has lessons
        $lessonCount = Lesson::where('module_id', $id)->count();
        if ($lessonCount > 0) {
            return $this->sendError('Cannot delete module.', ['error' => 'This module contains lessons. Delete all lessons first.'], 422);
        }

        $module->delete();

        return $this->sendResponse([], 'Module deleted successfully.');
    }
}
