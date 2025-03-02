<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('forum_threads', function (Blueprint $table) {
        if (!Schema::hasColumn('forum_threads', 'deleted_at')) {
            $table->softDeletes();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Removes `deleted_at` column if rollback
        });
    }
};
