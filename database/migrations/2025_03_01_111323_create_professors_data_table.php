<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessorsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('professors_data', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key referencing users table
            $table->string('position'); // Job position of the professor
            $table->string('company'); // Company/Institution the professor is affiliated with
            $table->enum('gender', ['Male', 'Female']); // Gender of the professor
            $table->date('birth_date'); // Birth date of the professor
            $table->text('work_experience_years')->nullable(); // Work experience description (optional)
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('professors_data');
    }
}
