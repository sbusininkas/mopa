<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_lessons_monday')->default(9);
            $table->unsignedTinyInteger('max_lessons_tuesday')->default(9);
            $table->unsignedTinyInteger('max_lessons_wednesday')->default(9);
            $table->unsignedTinyInteger('max_lessons_thursday')->default(9);
            $table->unsignedTinyInteger('max_lessons_friday')->default(9);
            $table->unsignedTinyInteger('max_same_subject_per_day')->default(3);
        });
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn([
                'max_lessons_monday',
                'max_lessons_tuesday',
                'max_lessons_wednesday',
                'max_lessons_thursday',
                'max_lessons_friday',
                'max_same_subject_per_day',
            ]);
        });
    }
};
