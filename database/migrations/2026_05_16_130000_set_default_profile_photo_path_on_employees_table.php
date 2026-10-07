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

        DB::statement("ALTER TABLE employees MODIFY profile_photo_path VARCHAR(255) NOT NULL DEFAULT 'profile-photos/.default-photo.jpg'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE employees MODIFY profile_photo_path VARCHAR(255) NOT NULL');
    }
};
