<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forum_comments', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links comment to a user
            $table->foreignId('thread_id')->constrained('forum_threads')->onDelete('cascade'); // Links comment to a forum thread
            $table->text('content'); // Comment content
            $table->foreignId('parent_id')->nullable()->constrained('forum_comments')->onDelete('cascade'); // If it's a reply to another comment
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_comments'); // Drops the table on rollback
    }
};
