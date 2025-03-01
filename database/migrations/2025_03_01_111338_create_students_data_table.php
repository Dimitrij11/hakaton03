<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_data', function (Blueprint $table) {
            $table->id(); // Primary key for the student data
            $table->unsignedBigInteger('user_id'); // Foreign key to link to the user table
            $table->enum('gender', ['Male', 'Female'])->nullable(); // Gender field
            $table->date('birth_date')->nullable(); // Birth date
            $table->string('school_year')->nullable(); // School year (e.g., Freshman, Sophomore)
            $table->string('field_of_study')->nullable(); // Field of study (e.g., Computer Science)
            $table->string('current_school')->nullable(); // Current school name
            $table->timestamps(); // Timestamps for created_at and updated_at

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_data');
    }
}
