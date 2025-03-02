<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Category;
use App\Models\Module;
use App\Models\ProfessorData;

class CourseAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        $courses = Course::with(['category', 'professors', 'modules'])->paginate(10);
        return response()->json($courses);
    }

    public function show($id)
    {
        $course = Course::with(['category', 'professors', 'modules', 'modules.lessons'])->findOrFail($id);
        return response()->json($course);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|integer',
            'price' => 'required|numeric',
            'level' => 'required|string|in:beginner,intermediate,advanced',
            'category_id' => 'required|exists:categories,id',
            'thumbnail' => 'nullable|string',
            'professor_ids' => 'sometimes|array',
            'professor_ids.*' => 'exists:professors_data,id',
        ]);

        $course = Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'duration' => $validated['duration'],
            'price' => $validated['price'],
            'level' => $validated['level'],
            'category_id' => $validated['category_id'],
            'thumbnail' => $validated['thumbnail'] ?? null,
        ]);

        if (isset($validated['professor_ids'])) {
            $course->professors()->attach($validated['professor_ids']);
        }

        return response()->json($course, 201);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'duration' => 'sometimes|integer',
            'price' => 'sometimes|numeric',
            'level' => 'sometimes|string|in:beginner,intermediate,advanced',
            'category_id' => 'sometimes|exists:categories,id',
            'thumbnail' => 'nullable|string',
            'professor_ids' => 'sometimes|array',
            'professor_ids.*' => 'exists:professors_data,id',
        ]);

        $course->update($validated);

        if (isset($validated['professor_ids'])) {
            $course->professors()->sync($validated['professor_ids']);
        }

        return response()->json($course);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    public function getCategories()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function getProfessors()
    {
        $professors = ProfessorData::with('user')->get();
        return response()->json($professors);
    }
}
