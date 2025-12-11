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
        Schema::table('timetable_groups', function (Blueprint $table) {
            // Flag to indicate this group can be merged with same subject groups
            $table->boolean('can_merge_with_same_subject')->default(false)->after('lessons_per_week');
            
            // Foreign key to another group this group is merged with
            $table->foreignId('merged_with_group_id')->nullable()->constrained('timetable_groups')->nullOnDelete()->after('can_merge_with_same_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetable_groups', function (Blueprint $table) {
            $table->dropForeign(['merged_with_group_id']);
            $table->dropColumn('can_merge_with_same_subject');
            $table->dropColumn('merged_with_group_id');
        });
    }
};
