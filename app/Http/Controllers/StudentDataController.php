<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentData;

class StudentDataController extends Controller
{
    // ✅ Get all student data
    public function index()
    {
        return response()->json(StudentData::all());
    }

    // ✅ Store new student record
    public function store(Request $request)
    {
        $request->validate([
            'gender' => 'required|in:male,female,other',
            'birth_date' => 'required|date',
            'school_year' => 'required|string',
            'field_of_study' => 'required|string',
            'current_school' => 'required|string',
        ]);

        $student = StudentData::create($request->all());

        return response()->json(['message' => 'Student data added successfully', 'student' => $student], 201);
    }

    // ✅ Show specific student data
    public function show($id)
    {
        $student = StudentData::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json($student);
    }

    // ✅ Update student data
    public function update(Request $request, $id)
    {
        $student = StudentData::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->update($request->all());

        return response()->json(['message' => 'Student data updated successfully', 'student' => $student]);
    }

    // ✅ Delete student data
    public function destroy($id)
    {
        $student = StudentData::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->delete();

        return response()->json(['message' => 'Student data deleted successfully']);
    }
}
