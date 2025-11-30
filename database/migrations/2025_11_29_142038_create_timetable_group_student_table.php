<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('timetable_group_student')) {
            Schema::create('timetable_group_student', function (Blueprint $table) {
                $table->id();
                $table->foreignId('timetable_group_id');
                $table->foreignId('login_key_id');
                $table->timestamps();
            });
        }

        Schema::table('timetable_group_student', function (Blueprint $table) {
            $table->foreign('timetable_group_id')->references('id')->on('timetable_groups')->onDelete('cascade');
            $table->foreign('login_key_id')->references('id')->on('login_keys')->onDelete('cascade');
            try { $table->unique(['timetable_group_id', 'login_key_id']); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_group_student');
    }
};
