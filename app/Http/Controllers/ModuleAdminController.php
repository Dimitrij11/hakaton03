<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Course;
use App\Models\Lesson;

class ModuleAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $courseId = $request->query('course_id');

        if ($courseId) {
            $modules = Module::where('course_id', $courseId)
                ->with('lessons')
                ->orderBy('order', 'asc')
                ->get();
        } else {
            $modules = Module::with(['course', 'lessons'])
                ->orderBy('course_id')
                ->orderBy('order')
                ->paginate(15);
        }

        return response()->json($modules);
    }

    public function show($id)
    {
        $module = Module::with(['course', 'lessons'])->findOrFail($id);
        return response()->json($module);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'order' => 'sometimes|integer',
        ]);

        // If order is not provided, make it the last one in the course
        if (!isset($validated['order'])) {
            $lastModule = Module::where('course_id', $validated['course_id'])
                ->orderBy('order', 'desc')
                ->first();

            $validated['order'] = $lastModule ? $lastModule->order + 1 : 1;
        }

        $module = Module::create($validated);

        return response()->json($module, 201);
    }

    public function update(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'course_id' => 'sometimes|exists:courses,id',
            'order' => 'sometimes|integer',
        ]);

        $module->update($validated);

        return response()->json($module);
    }

    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();

        return response()->json(['message' => 'Module deleted successfully']);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer',
        ]);

        foreach ($validated['modules'] as $moduleData) {
            Module::where('id', $moduleData['id'])->update(['order' => $moduleData['order']]);
        }

        return response()->json(['message' => 'Modules reordered successfully']);
    }
}
