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
        Schema::create('timetable_group_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_group_id')->constrained('timetable_groups')->onDelete('cascade');
            $table->foreignId('copy_group_id')->constrained('timetable_groups')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate relationships
            $table->unique(['original_group_id', 'copy_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_group_copies');
    }
};
