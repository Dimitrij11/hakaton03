<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_responses', function (Blueprint $table) {
            $table->id(); // Primary key for the user response record
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links to the user who responded
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade'); // Links to the specific question being answered
            $table->foreignId('answer_id')->constrained('answers')->onDelete('cascade'); // Links to the selected answer
            $table->boolean('is_correct')->default(false); // Flag to indicate whether the answer was correct
            $table->timestamp('answered_at')->nullable(); // Time when the user answered the question
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_responses'); // Drops the user_responses table on rollback
    }
};
