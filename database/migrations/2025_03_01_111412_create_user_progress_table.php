<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links to users table
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade'); // Links to lessons table
            $table->boolean('completed')->default(false); // Tracks if the lesson is completed
            $table->timestamp('completed_at')->nullable(); // Records when the lesson was completed
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_progress'); // Drops the table on rollback
    }
};
