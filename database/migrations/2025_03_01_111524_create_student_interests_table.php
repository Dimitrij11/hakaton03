<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_interests', function (Blueprint $table) {
            $table->id(); // Primary key for the record
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Link to the user (student)
            $table->foreignId('interest_id')->constrained('interests')->onDelete('cascade'); // Link to the interest
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_interests'); // Drops the table on rollback
    }
};
