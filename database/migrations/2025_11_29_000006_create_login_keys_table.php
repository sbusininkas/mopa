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
        Schema::create('login_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('key')->unique(); // Unique login key/code
            $table->enum('type', ['student', 'teacher']); // Who can use this key
            $table->string('first_name')->nullable(); // For students: first name
            $table->string('last_name')->nullable(); // For students: last name
            $table->string('email')->nullable(); // Used as email after registration
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Link to user after registration
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null'); // For students: which class
            $table->boolean('used')->default(false); // Has this key been used to register?
            $table->timestamps();

            $table->index(['school_id', 'type']);
            $table->index(['key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_keys');
    }
};
