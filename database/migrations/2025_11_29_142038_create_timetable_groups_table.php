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
        Schema::create('timetable_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            // Link to a teacher via LoginKey (type=teacher)
            $table->foreignId('teacher_login_key_id')->nullable()->constrained('login_keys')->nullOnDelete();
            // New fields for room, week type, lessons per week
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->enum('week_type', ['all', 'even', 'odd'])->default('all');
            $table->unsignedTinyInteger('lessons_per_week')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_groups');
    }
};
