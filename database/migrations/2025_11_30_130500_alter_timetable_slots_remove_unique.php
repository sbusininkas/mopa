<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('timetable_slots', function (Blueprint $table) {
			// Add single column index so foreign key on timetable_id remains indexed
			$table->index('timetable_id');
			// Drop existing unique constraint on (timetable_id, day, slot)
			$table->dropUnique('timetable_slots_timetable_id_day_slot_unique');
			// Replace with non-unique composite index for query performance
			$table->index(['timetable_id', 'day', 'slot']);
		});
	}

	public function down(): void
	{
		Schema::table('timetable_slots', function (Blueprint $table) {
			$table->dropIndex(['timetable_id', 'day', 'slot']);
			// Drop the single column index; unique will cover timetable_id
			$table->dropIndex(['timetable_id']);
			$table->unique(['timetable_id', 'day', 'slot']);
		});
	}
};