<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id(); // Primary key for the subscription record
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Links subscription to a user
            $table->string('email')->unique(); // The user's email for receiving the newsletter
            $table->enum('subscription_at', ['active', 'inactive'])->default('active'); // Subscription status
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions'); // Drops the table on rollback
    }
};
