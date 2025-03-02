<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\UserResponse;

class QuizAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    // Quiz Methods
    public function indexQuizzes(Request $request)
    {
        $lessonId = $request->query('lesson_id');

        if ($lessonId) {
            $quizzes = Quiz::where('lesson_id', $lessonId)->with('questions')->get();
        } else {
            $quizzes = Quiz::with(['lesson', 'questions'])->paginate(15);
        }

        return response()->json($quizzes);
    }

    public function showQuiz($id)
    {
        $quiz = Quiz::with(['lesson', 'questions.answers'])->findOrFail($id);
        return response()->json($quiz);
    }

    public function storeQuiz(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'lesson_id' => 'required|exists:lessons,id',
            'passing_score' => 'required|integer',
            'time_limit' => 'nullable|integer',
        ]);

        $quiz = Quiz::create($validated);

        return response()->json($quiz, 201);
    }

    public function updateQuiz(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'lesson_id' => 'sometimes|exists:lessons,id',
            'passing_score' => 'sometimes|integer',
            'time_limit' => 'nullable|integer',
        ]);

        $quiz->update($validated);

        return response()->json($quiz);
    }

    public function destroyQuiz($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    // Question Methods
    public function indexQuestions(Request $request)
    {
        $quizId = $request->query('quiz_id');

        if ($quizId) {
            $questions = Question::where('quiz_id', $quizId)
                ->with('answers')
                ->orderBy('order', 'asc')
                ->get();
        } else {
            $questions = Question::with(['quiz', 'answers'])
                ->orderBy('quiz_id')
                ->orderBy('order')
                ->paginate(15);
        }

        return response()->json($questions);
    }

    public function showQuestion($id)
    {
        $question = Question::with(['quiz', 'answers'])->findOrFail($id);
        return response()->json($question);
    }

    public function storeQuestion(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'quiz_id' => 'required|exists:quizzes,id',
            'type' => 'required|string|in:multiple_choice,single_choice,text',
            'order' => 'sometimes|integer',
            'points' => 'required|integer',
        ]);

        // If order is not provided, make it the last one in the quiz
        if (!isset($validated['order'])) {
            $lastQuestion = Question::where('quiz_id', $validated['quiz_id'])
                ->orderBy('order', 'desc')
                ->first();

            $validated['order'] = $lastQuestion ? $lastQuestion->order + 1 : 1;
        }

        $question = Question::create($validated);

        return response()->json($question, 201);
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'content' => 'sometimes|string',
            'quiz_id' => 'sometimes|exists:quizzes,id',
            'type' => 'sometimes|string|in:multiple_choice,single_choice,text',
            'order' => 'sometimes|integer',
            'points' => 'sometimes|integer',
        ]);

        $question->update($validated);

        return response()->json($question);
    }

    public function destroyQuestion($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return response()->json(['message' => 'Question deleted successfully']);
    }

    // Answer Methods
    public function indexAnswers(Request $request)
    {
        $questionId = $request->query('question_id');

        if ($questionId) {
            $answers = Answer::where('question_id', $questionId)->get();
        } else {
            $answers = Answer::with('question')->paginate(15);
        }

        return response()->json($answers);
    }

    public function showAnswer($id)
    {
        $answer = Answer::with('question')->findOrFail($id);
        return response()->json($answer);
    }

    public function storeAnswer(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'question_id' => 'required|exists:questions,id',
            'is_correct' => 'required|boolean',
            'explanation' => 'nullable|string',
        ]);

        $answer = Answer::create($validated);

        return response()->json($answer, 201);
    }

    public function updateAnswer(Request $request, $id)
    {
        $answer = Answer::findOrFail($id);

        $validated = $request->validate([
            'content' => 'sometimes|string',
            'question_id' => 'sometimes|exists:questions,id',
            'is_correct' => 'sometimes|boolean',
            'explanation' => 'nullable|string',
        ]);

        $answer->update($validated);

        return response()->json($answer);
    }

    public function destroyAnswer($id)
    {
        $answer = Answer::findOrFail($id);
        $answer->delete();

        return response()->json(['message' => 'Answer deleted successfully']);
    }

    // User Responses
    public function getUserResponses(Request $request)
    {
        $quizId = $request->query('quiz_id');
        $userId = $request->query('user_id');

        $query = UserResponse::with(['user', 'question', 'answer']);

        if ($quizId) {
            $query->whereHas('question', function ($q) use ($quizId) {
                $q->where('quiz_id', $quizId);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $responses = $query->paginate(15);

        return response()->json($responses);
    }
}
