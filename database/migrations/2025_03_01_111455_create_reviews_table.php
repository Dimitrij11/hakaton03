<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); // Primary key for the review record
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links review to a user
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // Links review to a course
            $table->tinyInteger('rating')->unsigned()->default(1); // Rating from 1 to 5 stars (1 is default)
            $table->text('comment')->nullable(); // Optional text field for comments
            $table->timestamps(); // Timestamps for created_at and updated_at
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews'); // Drops the table on rollback
    }
};
