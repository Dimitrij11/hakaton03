<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Models\Enroll;
use Illuminate\Support\Facades\Auth;
use DB;

class QuizAttemptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Get quiz to start attempt
    public function getQuiz($id)
    {
        $quiz = Quiz::with(['module.course', 'questions.options' => function ($query) {
            // Only return the option text, not whether it's correct
            $query->select('id', 'question_id', 'option_text');
        }])->findOrFail($id);

        // Check if the user is enrolled in the course
        $isEnrolled = Enroll::where('user_id', Auth::id())
            ->where('course_id', $quiz->module->course->id)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        // Check if already attempted and passed
        $existingAttempt = QuizAttempt::where('user_id', Auth::id())
            ->where('quiz_id', $id)
            ->where('is_passed', true)
            ->first();

        if ($existingAttempt) {
            return response()->json([
                'message' => 'You have already passed this quiz',
                'attempt' => $existingAttempt
            ]);
        }

        // Create a new attempt
        $attempt = QuizAttempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $id,
            'start_time' => now(),
            'status' => 'in_progress'
        ]);

        return response()->json([
            'quiz' => $quiz,
            'attempt_id' => $attempt->id
        ]);
    }

    // Submit quiz answers
    public function submitQuiz(Request $request, $attemptId)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.option_id' => 'required|exists:question_options,id',
        ]);

        $attempt = QuizAttempt::findOrFail($attemptId);

        // Check if this attempt belongs to the authenticated user
        if ($attempt->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if the attempt is already completed
        if ($attempt->status !== 'in_progress') {
            return response()->json(['message' => 'This attempt is already completed'], 422);
        }

        $quiz = Quiz::with('questions.options')->findOrFail($attempt->quiz_id);
        $totalQuestions = $quiz->questions->count();
        $correctAnswers = 0;

        // Process each answer
        foreach ($validated['answers'] as $answer) {
            $question = QuizQuestion::findOrFail($answer['question_id']);
            $option = QuestionOption::findOrFail($answer['option_id']);

            // Save the student's answer
            $studentAnswer = StudentAnswer::create([
                'attempt_id' => $attemptId,
                'question_id' => $answer['question_id'],
                'option_id' => $answer['option_id'],
            ]);

            // Check if the answer is correct
            if ($option->is_correct) {
                $correctAnswers++;
            }
        }

        // Calculate score
        $score = ($totalQuestions > 0) ? ($correctAnswers / $totalQuestions) * 100 : 0;
        $isPassed = $score >= $quiz->passing_score;

        // Update the attempt
        $attempt->update([
            'end_time' => now(),
            'score' => $score,
            'is_passed' => $isPassed,
            'status' => 'completed'
        ]);

        return response()->json([
            'message' => $isPassed ? 'Quiz passed!' : 'Quiz not passed. Try again.',
            'attempt' => $attempt,
            'score' => $score,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions
        ]);
    }

    // Get quiz attempts history
    public function attemptHistory($quizId = null)
    {
        $query = QuizAttempt::with('quiz')
            ->where('user_id', Auth::id());

        if ($quizId) {
            $query->where('quiz_id', $quizId);
        }

        $attempts = $query->orderBy('created_at', 'desc')->get();

        return response()->json($attempts);
    }

    // Get quiz attempt details with correct answers (after completion)
    public function attemptDetails($attemptId)
    {
        $attempt = QuizAttempt::with([
            'quiz',
            'answers.question',
            'answers.selectedOption'
        ])->findOrFail($attemptId);

        // Check if the attempt belongs to the authenticated user
        if ($attempt->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if the attempt is completed
        if ($attempt->status !== 'completed') {
            return response()->json(['message' => 'This attempt is not completed yet'], 422);
        }

        // Include correct answers for review
        foreach ($attempt->answers as $answer) {
            $correctOption = QuestionOption::where('question_id', $answer->question_id)
                ->where('is_correct', true)
                ->first();

            $answer->correct_option = $correctOption;
            $answer->is_correct = $answer->option_id === $correctOption->id;
        }

        return response()->json($attempt);
    }
}
