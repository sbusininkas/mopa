<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timetable_teacher_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_login_key_id')->constrained('login_keys')->onDelete('cascade');
            $table->unsignedTinyInteger('day_of_week'); // 1-5 (Mon-Fri)
            $table->time('start_time'); // HH:MM:SS
            $table->time('end_time');   // HH:MM:SS
            $table->timestamps();

            // Short explicit index name to avoid MySQL 64-char limit
            $table->index(['timetable_id','teacher_login_key_id','day_of_week'], 'tt_unavail_t_tk_dow_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_teacher_unavailabilities');
    }
};
