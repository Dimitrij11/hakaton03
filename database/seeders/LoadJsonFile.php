<?php

namespace Database\Seedergis;

use Illuminate\Database\Seeder;
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

class LoadJsonDataSeeder extends Seeder
{
    public function run()
    {
        // Load data for each model with its corresponding JSON file
        $this->loadModelData(Category::class, 'categories.json');
        $this->loadModelData(Course::class, 'courses.json');
        $this->loadModelData(CourseProfessor::class, 'course_professors.json');
        $this->loadModelData(ForumComment::class, 'forum_comments.json');
        $this->loadModelData(ForumThread::class, 'forum_threads.json');
        $this->loadModelData(Interest::class, 'interests.json');
        $this->loadModelData(NewsletterSubscription::class, 'newsletter_subscriptions.json');
        $this->loadModelData(StudentInterest::class, 'student_interests.json');
        $this->loadModelData(Lesson::class, 'lessons.json');
        $this->loadModelData(Message::class, 'messages.json');
        $this->loadModelData(Module::class, 'modules.json');
        $this->loadModelData(ProfessorData::class, 'professors_data.json');
        $this->loadModelData(Review::class, 'reviews.json');
        $this->loadModelData(Role::class, 'roles.json');
        $this->loadModelData(StudentData::class, 'student_data.json');
        $this->loadModelData(User::class, 'users.json');
        $this->loadModelData(UserProgress::class, 'user_progress.json');
        $this->loadModelData(View::class, 'views.json');
    }

    // Helper function to load JSON data into a given model
    private function loadModelData($model, $filename)
    {
        $filePath = storage_path("app/json/{$filename}");

        if (file_exists($filePath)) {
            $jsonData = json_decode(file_get_contents($filePath), true);

            foreach ($jsonData as $data) {
                // Adjust this to the model's structure as needed
                $model::create($data); // Assumes JSON structure matches database structure
            }
        }
    }
}
