<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('views', function (Blueprint $table) {
            if (!Schema::hasColumn('views', 'viewed_at')) {
                $table->timestamp('viewed_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('views', function (Blueprint $table) {
            $table->dropColumn('viewed_at');
        });
    }
};


