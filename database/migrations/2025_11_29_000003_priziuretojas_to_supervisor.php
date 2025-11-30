<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ensure 'supervisor' exists in enum
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student','priziuretojas','supervisor') NOT NULL DEFAULT 'student'");

        // migrate existing priziuretojas users to supervisor
        DB::table('users')->where('role', 'priziuretojas')->update(['role' => 'supervisor']);

        // now remove 'priziuretojas' from enum
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student','supervisor') NOT NULL DEFAULT 'student'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // add priziuretojas back
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student','supervisor','priziuretojas') NOT NULL DEFAULT 'student'");
        // change supervisor back to priziuretojas (best-effort)
        DB::table('users')->where('role', 'supervisor')->update(['role' => 'priziuretojas']);
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student','priziuretojas') NOT NULL DEFAULT 'student'");
    }
};
