<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->string('generation_status')->nullable()->index(); // running, completed, failed
            $table->unsignedInteger('generation_progress')->default(0); // 0..100
            $table->timestamp('generation_started_at')->nullable();
            $table->timestamp('generation_finished_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn(['generation_status','generation_progress','generation_started_at','generation_finished_at']);
        });
    }
};
