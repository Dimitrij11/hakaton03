<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Category;
use App\Models\ProfessorCourse;
use App\Models\Enroll;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfessorCourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('professor');
    }

    // Get all courses created by the professor
    public function index()
    {
        $professorId = Auth::id();

        $courses = Course::whereHas('professors', function ($query) use ($professorId) {
            $query->where('user_id', $professorId);
        })->with(['category', 'modules', 'enrollments'])->get();

        // Add enrollment count
        foreach ($courses as $course) {
            $course->enrollment_count = $course->enrollments->count();
        }

        return response()->json($courses);
    }

    // Create a new course
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'level' => 'required|string|in:beginner,intermediate,advanced',
            'thumbnail' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
            'duration' => 'nullable|integer',
            'price' => 'nullable|numeric',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $validated['thumbnail'] = $path;
        }

        // Set default values
        $validated['is_published'] = $request->has('is_published') ? $request->is_published : false;
        $validated['slug'] = Str::slug($validated['title']);

        // Create course
        $course = Course::create($validated);

        // Associate professor with the course
        ProfessorCourse::create([
            'user_id' => Auth::id(),
            'course_id' => $course->id,
        ]);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course->load(['category', 'professors.user'])
        ], 201);
    }

    // Get a specific course
    public function show($id)
    {
        $course = $this->getCourseWithAuthorization($id);

        // Count enrollments
        $enrollmentCount = Enroll::where('course_id', $id)->count();

        // Get modules and lessons
        $course->load(['modules.lessons', 'category', 'enrollments.user']);

        $course->enrollment_count = $enrollmentCount;

        return response()->json($course);
    }

    // Update a course
    public function update(Request $request, $id)
    {
        $course = $this->getCourseWithAuthorization($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'level' => 'sometimes|string|in:beginner,intermediate,advanced',
            'thumbnail' => 'nullable|image|max:2048',
            'is_published' => 'sometimes|boolean',
            'duration' => 'nullable|integer',
            'price' => 'nullable|numeric',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($course->thumbnail && Storage::disk('public')->exists($course->thumbnail)) {
                Storage::disk('public')->delete($course->thumbnail);
            }

            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $validated['thumbnail'] = $path;
        }

        // Update slug if title changes
        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course->load(['category', 'professors.user'])
        ]);
    }

    // Delete a course
    public function destroy($id)
    {
        $course = $this->getCourseWithAuthorization($id);

        // Delete thumbnail if exists
        if ($course->thumbnail && Storage::disk('public')->exists($course->thumbnail)) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        // Delete related modules and lessons
        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                $lesson->delete();
            }
            $module->delete();
        }

        // Delete professor associations
        $course->professors()->detach();

        // Delete course
        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully'
        ]);
    }

    // Add a module to a course
    public function addModule(Request $request, $courseId)
    {
        $course = $this->getCourseWithAuthorization($courseId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        // Set order to be the last if not provided
        if (!isset($validated['order'])) {
            $lastModule = Module::where('course_id', $courseId)
                ->orderBy('order', 'desc')
                ->first();

            $validated['order'] = $lastModule ? $lastModule->order + 1 : 1;
        }

        $validated['course_id'] = $courseId;

        $module = Module::create($validated);

        return response()->json([
            'message' => 'Module added successfully',
            'module' => $module
        ], 201);
    }

    // Add a lesson to a module
    public function addLesson(Request $request, $moduleId)
    {
        $module = Module::with('course')->findOrFail($moduleId);

        // Check if the professor has access to the course
        $this->getCourseWithAuthorization($module->course_id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'order' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        // Set order to be the last if not provided
        if (!isset($validated['order'])) {
            $lastLesson = Lesson::where('module_id', $moduleId)
                ->orderBy('order', 'desc')
                ->first();

            $validated['order'] = $lastLesson ? $lastLesson->order + 1 : 1;
        }

        $validated['module_id'] = $moduleId;

        // Handle attachments
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('lesson_attachments', 'public');
                $attachmentPaths[] = $path;
            }
            $validated['attachments'] = json_encode($attachmentPaths);
        }

        $lesson = Lesson::create($validated);

        return response()->json([
            'message' => 'Lesson added successfully',
            'lesson' => $lesson
        ], 201);
    }

    // Update a module
    public function updateModule(Request $request, $moduleId)
    {
        $module = Module::with('course')->findOrFail($moduleId);

        // Check if the professor has access to the course
        $this->getCourseWithAuthorization($module->course_id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order' => 'sometimes|integer',
        ]);

        $module->update($validated);

        return response()->json([
            'message' => 'Module updated successfully',
            'module' => $module
        ]);
    }

    // Update a lesson
    public function updateLesson(Request $request, $lessonId)
    {
        $lesson = Lesson::with('module.course')->findOrFail($lessonId);

        // Check if the professor has access to the course
        $this->getCourseWithAuthorization($lesson->module->course_id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'video_url' => 'nullable|url',
            'order' => 'sometimes|integer',
            'duration' => 'nullable|integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            // Delete old attachments
            if ($lesson->attachments) {
                $oldAttachments = json_decode($lesson->attachments, true);
                foreach ($oldAttachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment)) {
                        Storage::disk('public')->delete($attachment);
                    }
                }
            }

            $attachmentPaths = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('lesson_attachments', 'public');
                $attachmentPaths[] = $path;
            }
            $validated['attachments'] = json_encode($attachmentPaths);
        }

        $lesson->update($validated);

        return response()->json([
            'message' => 'Lesson updated successfully',
            'lesson' => $lesson
        ]);
    }

    // Delete a module
    public function deleteModule($moduleId)
    {
        $module = Module::with(['course', 'lessons'])->findOrFail($moduleId);

        // Check if the professor has access to the course
        $this->getCourseWithAuthorization($module->course_id);

        // Delete all lessons in the module
        foreach ($module->lessons as $lesson) {
            if ($lesson->attachments) {
                $attachments = json_decode($lesson->attachments, true);
                foreach ($attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment)) {
                        Storage::disk('public')->delete($attachment);
                    }
                }
            }
            $lesson->delete();
        }

        // Delete the module
        $module->delete();

        return response()->json([
            'message' => 'Module deleted successfully'
        ]);
    }

    // Delete a lesson
    public function deleteLesson($lessonId)
    {
        $lesson = Lesson::with('module.course')->findOrFail($lessonId);

        // Check if the professor has access to the course
        $this->getCourseWithAuthorization($lesson->module->course_id);

        // Delete attachments
        if ($lesson->attachments) {
            $attachments = json_decode($lesson->attachments, true);
            foreach ($attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment)) {
                    Storage::disk('public')->delete($attachment);
                }
            }
        }

        // Delete the lesson
        $lesson->delete();

        return response()->json([
            'message' => 'Lesson deleted successfully'
        ]);
    }

    // Get students enrolled in a course
    public function getEnrolledStudents($courseId)
    {
        $course = $this->getCourseWithAuthorization($courseId);

        $enrollments = Enroll::with('user')
            ->where('course_id', $courseId)
            ->orderBy('enrollment_date', 'desc')
            ->get();

        return response()->json($enrollments);
    }

    // Helper method to get a course and check professor authorization
    private function getCourseWithAuthorization($courseId)
    {
        $professorId = Auth::id();

        $course = Course::whereHas('professors', function ($query) use ($professorId) {
            $query->where('user_id', $professorId);
        })->findOrFail($courseId);

        return $course;
    }
}
