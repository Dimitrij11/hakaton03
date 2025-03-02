<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // Primary key for the course
            $table->string('title'); // Title of the course
            $table->unsignedBigInteger('instructor_id'); // Changed from professor_id to instructor_id
            $table->foreign('instructor_id')->references('id')->on('professors_data')->onDelete('cascade');
            $table->text('description'); // Detailed description of the course
            $table->unsignedBigInteger('category_id'); // Foreign key to the category table
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->timestamps(); // Timestamps for created_at and updated_at
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
