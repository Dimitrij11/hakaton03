<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->string('name'); // Name of the user
            $table->string('email')->unique(); // Unique email for login
            $table->string('password'); // Encrypted password
            $table->string('profile_picture')->nullable(); // Nullable profile picture field
            $table->enum('role_id', ['admin', 'professor', 'student'])->default('student'); // Enum for user role
            $table->timestamps(); // Adds created_at and updated_at
        });



    }

    public function down(): void
    {
        Schema::dropIfExists('users'); // Drops the table if the migration is rolled back
    }
};
