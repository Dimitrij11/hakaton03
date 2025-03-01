<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('views', function (Blueprint $table) {
            $table->id(); // Primary key for the record
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Link to the user (who viewed the lesson)
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade'); // Link to the lesson that was viewed
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('views'); // Drops the table on rollback
    }
};

