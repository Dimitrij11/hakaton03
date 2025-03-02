<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Module;

class LessonAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $moduleId = $request->query('module_id');

        if ($moduleId) {
            $lessons = Lesson::where('module_id', $moduleId)
                ->orderBy('order', 'asc')
                ->get();
        } else {
            $lessons = Lesson::with('module.course')
                ->orderBy('module_id')
                ->orderBy('order')
                ->paginate(15);
        }

        return response()->json($lessons);
    }

    public function show($id)
    {
        $lesson = Lesson::with('module.course')->findOrFail($id);
        return response()->json($lesson);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'module_id' => 'required|exists:modules,id',
            'order' => 'sometimes|integer',
            'duration' => 'required|integer',
            'type' => 'required|string|in:video,text,interactive',
            'resource_url' => 'nullable|string',
        ]);

        // If order is not provided, make it the last one in the module
        if (!isset($validated['order'])) {
            $lastLesson = Lesson::where('module_id', $validated['module_id'])
                ->orderBy('order', 'desc')
                ->first();

            $validated['order'] = $lastLesson ? $lastLesson->order + 1 : 1;
        }

        $lesson = Lesson::create($validated);

        return response()->json($lesson, 201);
    }

    public function update(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'module_id' => 'sometimes|exists:modules,id',
            'order' => 'sometimes|integer',
            'duration' => 'sometimes|integer',
            'type' => 'sometimes|string|in:video,text,interactive',
            'resource_url' => 'nullable|string',
        ]);

        $lesson->update($validated);

        return response()->json($lesson);
    }

    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->delete();

        return response()->json(['message' => 'Lesson deleted successfully']);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|exists:lessons,id',
            'lessons.*.order' => 'required|integer',
        ]);

        foreach ($validated['lessons'] as $lessonData) {
            Lesson::where('id', $lessonData['id'])->update(['order' => $lessonData['order']]);
        }

        return response()->json(['message' => 'Lessons reordered successfully']);
    }
}
