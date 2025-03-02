<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Achievement;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseProfessor;
use App\Models\Enroll;
use App\Models\Forum;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\ForumPostLike;
use App\Models\ForumThread;
use App\Models\Interest;
use App\Models\Lesson;
use App\Models\Message;
use App\Models\Module;
use App\Models\NewsletterSubscription;
use App\Models\ProfessorCourse;
use App\Models\ProfessorData;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Review;
use App\Models\Role;
use App\Models\StudentAnswer;
use App\Models\StudentData;
use App\Models\StudentInterest;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\UserResponse;
use App\Models\View;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LoadJsonDataSeeder extends Seeder
{
    use WithoutModelEvents; // ✅ Ensure WithoutModelEvents is properly used

    public function run()
    {
        // ✅ Disable Foreign Key Checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ✅ Truncate Tables
        $this->truncateTables([
            'roles',
            'categories',
            'interests',
            'users',
            'professor_data', // Changed from professors_data to match model convention
            'student_data',
            'courses',
            'modules',
            'lessons',
            'course_professor',
            'student_interests',
            'user_progress',
            'forum_threads',
            'forum_comments',
            'messages',
            'newsletter_subscriptions',
            'reviews',
            'views'
        ]);

        // ✅ Seed JSON Data
        foreach ($this->getModelFileMap() as $model => $file) {
            $this->loadModelData($model, $file);
        }

        // ✅ Enable Foreign Key Checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Get the mapping of models to JSON files.
     */
    private function getModelFileMap()
    {
        return [
            Role::class => 'roles.json',
            Category::class => 'categories.json',
            Interest::class => 'interests.json',
            User::class => 'users.json',
            ProfessorData::class => 'professors_data.json',
            StudentData::class => 'student_data.json',
            Course::class => 'courses.json',
            Module::class => 'modules.json',
            Lesson::class => 'lessons.json',
            CourseProfessor::class => 'course_professor.json',
            StudentInterest::class => 'student_interests.json',
            UserProgress::class => 'user_progress.json',
            ForumThread::class => 'forum_threads.json',
            ForumComment::class => 'forum_comments.json',
            Message::class => 'messages.json',
            NewsletterSubscription::class => 'newsletter_subscriptions.json',
            Review::class => 'reviews.json',
            View::class => 'views.json',
            // Added missing mappings
            Achievement::class => 'achievements.json',
            Answer::class => 'answers.json',
            Question::class => 'questions.json',
            Quiz::class => 'quizzes.json',
            UserResponse::class => 'user_responses.json',
            Wishlist::class => 'wishlist.json',
            // These models may not have JSON files yet, comment if needed
            // QuizAttempt::class => 'quiz_attempts.json',
            // QuizQuestion::class => 'quiz_questions.json',
            // QuestionOption::class => 'question_options.json',
            // StudentAnswer::class => 'student_answers.json',
            // Enroll::class => 'enrolls.json',
            // Forum::class => 'forums.json',
            // ForumPost::class => 'forum_posts.json',
            // ForumPostLike::class => 'forum_post_likes.json',
            // ProfessorCourse::class => 'professor_courses.json',
        ];
    }

    /**
     * Truncate multiple tables safely.
     */
    private function truncateTables(array $tables)
    {
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }

    /**
     * Load data from a JSON file into the database.
     */
    private function loadModelData($model, $filename)
    {
        // Changed path from json_file to json_data to match actual directory
        $filePath = \storage_path("app/json_data/{$filename}");

        if (!file_exists($filePath)) {
            if (isset($this->command)) {
                $this->command->warn("❌ JSON file not found: {$filename}");
            }
            return;
        }

        $jsonData = json_decode(file_get_contents($filePath), true);

        if (!$jsonData) {
            if (isset($this->command)) {
                $this->command->warn("⚠️ JSON file is empty or invalid: {$filename}");
            }
            return;
        }

        foreach ($jsonData as $data) {
            // Fixed the View model check to be within the loop
            if ($model === View::class && isset($data['viewed_at'])) {
                $data['viewed_at'] = $this->formatDate($data['viewed_at']);
            }

            if (isset($data['password_hash'])) {
                $data['password'] = \bcrypt($data['password_hash']);
                unset($data['password_hash']);
            }

            if ($model === CourseProfessor::class && isset($data['user_id'])) {
                $data['professor_id'] = $data['user_id'];
                unset($data['user_id']);
            }

            if (isset($data['subscribed_at'])) {
                $data['subscription_at'] = $data['subscribed_at']; // Rename it
                unset($data['subscribed_at']); // Remove old field
            }

            $data['created_at'] = $this->formatDate($data['created_at'] ?? null);
            $data['updated_at'] = $this->formatDate($data['updated_at'] ?? null);

            $model::create($data);
        }

        if (isset($this->command)) {
            $this->command->info("✅ {$filename} seeded successfully!");
        }
    }

    /**
     * Format timestamps to MySQL DATETIME format.
     */
    private function formatDate($date)
    {
        if (!$date) {
            return \now();
        }

        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return \now();
        }
    }
}
