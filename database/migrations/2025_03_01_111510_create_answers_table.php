<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id(); // Primary key for the answer
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade'); // Links to a specific question
            $table->string('answer_text'); // The text of the answer choice
            $table->boolean('is_correct')->default(false); // Flag for the correct answer
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers'); // Drops the answers table on rollback
    }
};
