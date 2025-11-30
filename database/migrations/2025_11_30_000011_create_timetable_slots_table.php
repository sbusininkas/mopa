<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timetable_group_id')->constrained()->cascadeOnDelete();
            $table->string('day'); // e.g., Mon/Tue or localized
            $table->unsignedInteger('slot'); // 1..N
            $table->timestamps();

            $table->unique(['timetable_id', 'day', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
    }
};
