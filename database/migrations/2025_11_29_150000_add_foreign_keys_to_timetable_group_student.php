<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // No-op: original pivot migration now correctly adds FKs and unique constraints.
    }

    public function down(): void
    {
        // No-op
    }
};
