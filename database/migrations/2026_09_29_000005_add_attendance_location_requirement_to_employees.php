<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'requires_attendance_location')) {
                $table->boolean('requires_attendance_location')->default(true)->after('weekly_holiday');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'requires_attendance_location')) {
                $table->dropColumn('requires_attendance_location');
            }
        });
    }
};
