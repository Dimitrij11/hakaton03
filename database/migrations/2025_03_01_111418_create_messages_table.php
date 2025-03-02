<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); // Sender of the message
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade'); // Receiver of the message
            $table->text('content'); // Message content
            $table->timestamp('sent_at')->useCurrent(); // Timestamp for when the message was sent
            $table->boolean('is_read')->default(false); // Flag to track if the message has been read
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages'); // Drops the table on rollback
    }
};
