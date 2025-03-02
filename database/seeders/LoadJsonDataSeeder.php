<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseProfessor;
use App\Models\ForumComment;
use App\Models\ForumThread;
use App\Models\Interest;
use App\Models\Lesson;
use App\Models\Message;
use App\Models\Module;
use App\Models\NewsletterSubscription;
use App\Models\ProfessorData;
use App\Models\Review;
use App\Models\Role;
use App\Models\StudentData;
use App\Models\StudentInterest;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\View;
use Carbon\Carbon;

class LoadJsonDataSeeder extends Seeder
{
    public function run()
    {
        // ✅ Step 1: Disable Foreign Key Checks before truncating tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ✅ Step 2: Truncate Tables (Order Matters)
        $this->truncateTables([
            'roles', 'categories', 'interests', // Independent tables
            'users', 'professors_data', 'student_data', // User-related tables
            'courses', 'modules', 'lessons', // Main entities
            'course_professor', 'student_interests', 'user_progress', // Relations
            'forum_threads', 'forum_comments', 'messages', // Forum & messaging
            'newsletter_subscriptions', 'reviews', 'views' // Other tables
        ]);

        // ✅ Step 3: Seed JSON Data into Models
        $this->loadModelData(Role::class, 'roles.json');
        $this->loadModelData(Category::class, 'categories.json');
        $this->loadModelData(Interest::class, 'interests.json');
        $this->loadModelData(User::class, 'users.json');
        $this->loadModelData(ProfessorData::class, 'professors_data.json');
        $this->loadModelData(StudentData::class, 'student_data.json');
        $this->loadModelData(Course::class, 'courses.json');
        $this->loadModelData(Module::class, 'modules.json');
        $this->loadModelData(Lesson::class, 'lessons.json');
        $this->loadModelData(CourseProfessor::class, 'course_professors.json');
        $this->loadModelData(StudentInterest::class, 'student_interests.json');
        $this->loadModelData(UserProgress::class, 'user_progress.json');
        $this->loadModelData(ForumThread::class, 'forum_threads.json');
        $this->loadModelData(ForumComment::class, 'forum_comments.json');
        $this->loadModelData(Message::class, 'messages.json');
        $this->loadModelData(NewsletterSubscription::class, 'newsletter_subscriptions.json');
        $this->loadModelData(Review::class, 'reviews.json');
        $this->loadModelData(View::class, 'views.json');

        // ✅ Step 4: Enable Foreign Key Checks after seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Helper function to truncate multiple tables safely.
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
     * Load data from a JSON file and insert it into the database.
     */
    private function loadModelData($model, $filename)
    {
        $filePath = storage_path("app/json_file/{$filename}");

        if (!file_exists($filePath)) {
            $this->command->warn("❌ JSON file not found: {$filename}");
            return;
        }

        $jsonData = json_decode(file_get_contents($filePath), true);

        if (!$jsonData) {
            $this->command->warn("⚠️ JSON file is empty or invalid: {$filename}");
            return;
        }

        foreach ($jsonData as $data) {
            // ✅ Hash password if it exists
            if (isset($data['password_hash'])) {
                $data['password'] = bcrypt($data['password_hash']);
                unset($data['password_hash']);
            }

            // ✅ Convert ISO 8601 timestamps to MySQL format
            $data['created_at'] = $this->formatDate($data['created_at'] ?? null);
            $data['updated_at'] = $this->formatDate($data['updated_at'] ?? null);

            // ✅ Insert into database
            $model::create($data);
        }

        $this->command->info("✅ {$filename} seeded successfully!");
    }

    /**
     * Format timestamps to MySQL DATETIME format.
     */
    private function formatDate($date)
    {
        if (!$date) {
            return now(); // Use current time if missing
        }

        try {
            return Carbon::createFromFormat('Y-m-d\TH:i:s', $date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return now(); // Fallback if parsing fails
        }
    }
}
