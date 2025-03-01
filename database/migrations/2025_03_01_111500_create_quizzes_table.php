<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id(); // Primary key for the quiz
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // Link to the course
            $table->string('title'); // Title of the quiz
            $table->text('description')->nullable(); // Optional description of the quiz
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes'); // Drops the quizzes table on rollback
    }
};
