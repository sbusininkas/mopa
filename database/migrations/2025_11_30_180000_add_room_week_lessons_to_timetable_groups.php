<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('timetable_groups', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('teacher_login_key_id')->constrained('rooms')->nullOnDelete();
            $table->enum('week_type', ['all', 'even', 'odd'])->default('all')->after('room_id');
            $table->unsignedTinyInteger('lessons_per_week')->default(1)->after('week_type');
        });
    }
    public function down(): void
    {
        Schema::table('timetable_groups', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn(['room_id', 'week_type', 'lessons_per_week']);
        });
    }
};
