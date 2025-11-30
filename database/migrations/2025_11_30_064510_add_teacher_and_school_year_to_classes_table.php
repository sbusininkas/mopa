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
        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('description')->constrained('login_keys')->nullOnDelete();
            }
            if (!Schema::hasColumn('classes', 'school_year')) {
                $table->string('school_year', 9)->nullable()->after('teacher_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'teacher_id')) {
                $table->dropConstrainedForeignId('teacher_id');
            }
            if (Schema::hasColumn('classes', 'school_year')) {
                $table->dropColumn('school_year');
            }
        });
    }
};
