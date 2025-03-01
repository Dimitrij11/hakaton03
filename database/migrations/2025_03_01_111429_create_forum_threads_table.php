<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id(); // Primary key for the thread
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links thread to a user
            $table->string('title'); // Title of the thread
            $table->text('content'); // Content or description of the thread
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_threads'); // Drops the table on rollback
    }
};
