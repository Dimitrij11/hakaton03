<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id(); // Primary key for the wishlist record
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links wishlist to a user
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // Links to a specific course
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists'); // Drops the table on rollback
    }
};
