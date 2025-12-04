<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('admin_key', 32)->unique()->nullable()->after('phone');
        });
        
        // Generate admin keys for existing schools
        DB::table('schools')->whereNull('admin_key')->get()->each(function ($school) {
            DB::table('schools')
                ->where('id', $school->id)
                ->update(['admin_key' => Str::random(12)]);
        });
        
        // Make admin_key required
        Schema::table('schools', function (Blueprint $table) {
            $table->string('admin_key', 32)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('admin_key');
        });
    }
};
