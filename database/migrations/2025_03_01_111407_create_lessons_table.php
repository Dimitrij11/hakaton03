<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade'); // Foreign key linking to modules
            $table->string('title'); // Title of the lesson
            $table->text('content')->nullable(); // Lesson content (optional)
            $table->string('video_url')->nullable(); // Video URL for the lesson (optional)
            $table->integer('order_number'); // Order of the lesson within the module
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons'); // Drops the table on rollback
    }
};
