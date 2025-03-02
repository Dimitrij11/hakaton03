<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Module;
use App\Models\Course;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfessorQuizController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('professor');
    }

    // Get all quizzes for a module
    public function getQuizzesByModule($moduleId)
    {
        $module = Module::with('course')->findOrFail($moduleId);

        // Check if professor has access to this module's course
        $this->checkProfessorAccess($module->course_id);

        $quizzes = Quiz::where('module_id', $moduleId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($quizzes);
    }

    // Get a specific quiz with its questions and options
    public function show($id)
    {
        $quiz = Quiz::with(['module.course', 'questions.options'])
            ->findOrFail($id);

        // Check if professor has access to this quiz's course
        $this->checkProfessorAccess($quiz->module->course_id);

        return response()->json($quiz);
    }

    // Create a new quiz
    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer', // In minutes
            'passing_score' => 'required|numeric|min:0|max:100',
            'is_published' => 'boolean',
            'max_attempts' => 'nullable|integer',
            'due_date' => 'nullable|date',
        ]);

        $module = Module::with('course')->findOrFail($validated['module_id']);

        // Check if professor has access to this module's course
        $this->checkProfessorAccess($module->course_id);

        // Set default values
        $validated['is_published'] = $request->has('is_published') ? $request->is_published : false;

        $quiz = Quiz::create($validated);

        return response()->json([
            'message' => 'Quiz created successfully',
            'quiz' => $quiz
        ], 201);
    }

    // Update a quiz
    public function update(Request $request, $id)
    {
        $quiz = Quiz::with('module.course')->findOrFail($id);

        // Check if professor has access to this quiz's course
        $this->checkProfessorAccess($quiz->module->course_id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer',
            'passing_score' => 'sometimes|numeric|min:0|max:100',
            'is_published' => 'boolean',
            'max_attempts' => 'nullable|integer',
            'due_date' => 'nullable|date',
        ]);

        $quiz->update($validated);

        return response()->json([
            'message' => 'Quiz updated successfully',
            'quiz' => $quiz
        ]);
    }

    // Delete a quiz
    public function destroy($id)
    {
        $quiz = Quiz::with(['module.course', 'questions.options', 'attempts'])
            ->findOrFail($id);

        // Check if professor has access to this quiz's course
        $this->checkProfessorAccess($quiz->module->course_id);

        // Start a transaction
        DB::beginTransaction();

        try {
            // Delete all student answers related to the quiz attempts
            foreach ($quiz->attempts as $attempt) {
                StudentAnswer::where('attempt_id', $attempt->id)->delete();
            }

            // Delete all quiz attempts
            $quiz->attempts()->delete();

            // Delete all questions and options
            foreach ($quiz->questions as $question) {
                // Delete all options for this question
                $question->options()->delete();
            }

            // Delete all questions
            $quiz->questions()->delete();

            // Delete quiz
            $quiz->delete();

            DB::commit();

            return response()->json([
                'message' => 'Quiz deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Add a question to a quiz
    public function addQuestion(Request $request, $quizId)
    {
        $quiz = Quiz::with('module.course')->findOrFail($quizId);

        // Check if professor has access to this quiz's course
        $this->checkProfessorAccess($quiz->module->course_id);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|string|in:single,multiple',
            'points' => 'required|numeric|min:0',
            'options' => 'required|array|min:2',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ]);

        // Create question
        $question = QuizQuestion::create([
            'quiz_id' => $quizId,
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'points' => $validated['points'],
        ]);

        // Create options
        $options = [];
        foreach ($validated['options'] as $optionData) {
            $option = QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $optionData['option_text'],
                'is_correct' => $optionData['is_correct'],
            ]);

            $options[] = $option;
        }

        return response()->json([
            'message' => 'Question added successfully',
            'question' => $question->load('options')
        ], 201);
    }

    // Update a question
    public function updateQuestion(Request $request, $questionId)
    {
        $question = QuizQuestion::with('quiz.module.course')->findOrFail($questionId);

        // Check if professor has access to this question's course
        $this->checkProfessorAccess($question->quiz->module->course_id);

        $validated = $request->validate([
            'question_text' => 'sometimes|string',
            'question_type' => 'sometimes|string|in:single,multiple',
            'points' => 'sometimes|numeric|min:0',
        ]);

        $question->update($validated);

        return response()->json([
            'message' => 'Question updated successfully',
            'question' => $question
        ]);
    }

    // Delete a question
    public function deleteQuestion($questionId)
    {
        $question = QuizQuestion::with('quiz.module.course', 'options')->findOrFail($questionId);

        // Check if professor has access to this question's course
        $this->checkProfessorAccess($question->quiz->module->course_id);

        // Delete all options for this question
        $question->options()->delete();

        // Delete question
        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully'
        ]);
    }

    // Add an option to a question
    public function addOption(Request $request, $questionId)
    {
        $question = QuizQuestion::with('quiz.module.course')->findOrFail($questionId);

        // Check if professor has access to this question's course
        $this->checkProfessorAccess($question->quiz->module->course_id);

        $validated = $request->validate([
            'option_text' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        $option = QuestionOption::create([
            'question_id' => $questionId,
            'option_text' => $validated['option_text'],
            'is_correct' => $validated['is_correct'],
        ]);

        return response()->json([
            'message' => 'Option added successfully',
            'option' => $option
        ], 201);
    }

    // Update an option
    public function updateOption(Request $request, $optionId)
    {
        $option = QuestionOption::with('question.quiz.module.course')->findOrFail($optionId);

        // Check if professor has access to this option's course
        $this->checkProfessorAccess($option->question->quiz->module->course_id);

        $validated = $request->validate([
            'option_text' => 'sometimes|string',
            'is_correct' => 'sometimes|boolean',
        ]);

        $option->update($validated);

        return response()->json([
            'message' => 'Option updated successfully',
            'option' => $option
        ]);
    }

    // Delete an option
    public function deleteOption($optionId)
    {
        $option = QuestionOption::with('question.quiz.module.course')->findOrFail($optionId);

        // Check if professor has access to this option's course
        $this->checkProfessorAccess($option->question->quiz->module->course_id);

        // Check if this would leave the question with less than 2 options
        $optionCount = QuestionOption::where('question_id', $option->question_id)->count();
        if ($optionCount <= 2) {
            return response()->json([
                'message' => 'Cannot delete option. Questions must have at least 2 options.'
            ], 422);
        }

        $option->delete();

        return response()->json([
            'message' => 'Option deleted successfully'
        ]);
    }

    // Get all quiz attempts for a specific quiz
    public function getQuizAttempts($quizId)
    {
        $quiz = Quiz::with('module.course')->findOrFail($quizId);

        // Check if professor has access to this quiz's course
        $this->checkProfessorAccess($quiz->module->course_id);

        $attempts = QuizAttempt::with(['user', 'answers.question', 'answers.selectedOption'])
            ->where('quiz_id', $quizId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($attempts);
    }

    // Get specific quiz attempt details
    public function getAttemptDetails($attemptId)
    {
        $attempt = QuizAttempt::with(['quiz.module.course', 'user', 'answers.question', 'answers.selectedOption'])
            ->findOrFail($attemptId);

        // Check if professor has access to this attempt's course
        $this->checkProfessorAccess($attempt->quiz->module->course_id);

        // For each answer, add correct option information
        foreach ($attempt->answers as $answer) {
            $correctOption = QuestionOption::where('question_id', $answer->question_id)
                ->where('is_correct', true)
                ->first();

            $answer->correct_option = $correctOption;
            $answer->is_correct = $answer->option_id === $correctOption->id;
        }

        return response()->json($attempt);
    }

    // Get quiz statistics
    public function getQuizStatistics($quizId)
    {
        $quiz = Quiz::with('module.course')->findOrFail($quizId);

        // Check if professor has access to this quiz's course
        $this->checkProfessorAccess($quiz->module->course_id);

        // Get all attempts for this quiz
        $attempts = QuizAttempt::where('quiz_id', $quizId)
            ->where('status', 'completed')
            ->get();

        // Calculate statistics
        $totalAttempts = $attempts->count();
        $averageScore = $attempts->avg('score');
        $passedAttempts = $attempts->where('is_passed', true)->count();
        $passRate = $totalAttempts > 0 ? ($passedAttempts / $totalAttempts) * 100 : 0;

        // Get question statistics
        $questions = QuizQuestion::where('quiz_id', $quizId)->get();
        $questionStats = [];

        foreach ($questions as $question) {
            $answers = StudentAnswer::whereHas('attempt', function ($query) use ($quizId) {
                $query->where('quiz_id', $quizId);
            })->where('question_id', $question->id)->get();

            $totalAnswers = $answers->count();

            // Get correct option
            $correctOption = QuestionOption::where('question_id', $question->id)
                ->where('is_correct', true)
                ->first();

            // Count correct answers
            $correctAnswers = $answers->filter(function ($answer) use ($correctOption) {
                return $answer->option_id === $correctOption->id;
            })->count();

            $correctRate = $totalAnswers > 0 ? ($correctAnswers / $totalAnswers) * 100 : 0;

            $questionStats[] = [
                'question' => $question,
                'total_answers' => $totalAnswers,
                'correct_answers' => $correctAnswers,
                'correct_rate' => $correctRate
            ];
        }

        return response()->json([
            'quiz' => $quiz,
            'total_attempts' => $totalAttempts,
            'average_score' => $averageScore,
            'passed_attempts' => $passedAttempts,
            'pass_rate' => $passRate,
            'question_statistics' => $questionStats
        ]);
    }

    // Helper method to check if the professor has access to a course
    private function checkProfessorAccess($courseId)
    {
        $professorId = Auth::id();

        $course = Course::whereHas('professors', function ($query) use ($professorId) {
            $query->where('user_id', $professorId);
        })->where('id', $courseId)->first();

        if (!$course) {
            abort(403, 'You do not have access to this resource.');
        }

        return $course;
    }
}
