<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id(); // Primary key for the interest record
            $table->string('name'); // Name of the interest/topic
            $table->text('description')->nullable(); // Optional description of the interest
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interests'); // Drops the interests table on rollback
    }
};
