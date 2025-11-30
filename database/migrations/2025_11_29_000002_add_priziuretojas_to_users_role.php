<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'priziuretojas' to the enum values. Use raw SQL to alter the column.
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student','priziuretojas') NOT NULL DEFAULT 'student'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student') NOT NULL DEFAULT 'student'");
    }
};
