<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timetable_teacher_working_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_login_key_id')->constrained('login_keys')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('1=Pirmadienis, 2=Antradienis, 3=Trečiadienis, 4=Ketvirtadienis, 5=Penktadienis');
            $table->timestamps();

            // Unique constraint: vienas mokytojas negali turėti tos pačios dienos du kartus tame pačiame tvarkaraštyje
            $table->unique(['timetable_id', 'teacher_login_key_id', 'day_of_week'], 'timetable_teacher_day_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_teacher_working_days');
    }
};
