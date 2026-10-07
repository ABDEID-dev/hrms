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
        Schema::table('employees', function (Blueprint $table) {
            // Carbon dayOfWeek: 0=Sunday, 1=Monday, ... 6=Saturday.
            $table->unsignedTinyInteger('weekly_holiday')->nullable()->comment('Weekly holiday day: 0=Sunday, 1=Monday, ... 6=Saturday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('weekly_holiday');
        });
    }
};
