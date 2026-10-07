<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE employees MODIFY degree VARCHAR(255) NOT NULL DEFAULT '-'");
        DB::statement("ALTER TABLE employees MODIFY address VARCHAR(255) NOT NULL DEFAULT '-'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE employees MODIFY degree VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE employees MODIFY address VARCHAR(255) NOT NULL');
    }
};
