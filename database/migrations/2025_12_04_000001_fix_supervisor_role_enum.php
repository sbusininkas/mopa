<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update 'priziuretojas' to 'supervisor'
        DB::table('users')
            ->where('role', 'priziuretojas')
            ->update(['role' => 'supervisor']);

        // Update enum to use 'supervisor' instead of 'priziuretojas'
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student','supervisor') NOT NULL DEFAULT 'student'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to 'priziuretojas'
        DB::table('users')
            ->where('role', 'supervisor')
            ->update(['role' => 'priziuretojas']);

        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student','priziuretojas') NOT NULL DEFAULT 'student'");
    }
};
