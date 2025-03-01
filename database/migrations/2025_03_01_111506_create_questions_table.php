<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id(); // Primary key for the question
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade'); // Links to a specific quiz
            $table->string('question_text'); // The text of the question
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer']); // Type of the question
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions'); // Drops the questions table on rollback
    }
};
