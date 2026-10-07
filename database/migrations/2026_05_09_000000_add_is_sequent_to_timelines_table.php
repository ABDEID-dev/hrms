<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('timelines', 'is_sequent')) {
            return;
        }

        Schema::table('timelines', function (Blueprint $table) {
            $table->boolean('is_sequent')->default(true)->after('end_date');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('timelines', 'is_sequent')) {
            return;
        }

        Schema::table('timelines', function (Blueprint $table) {
            $table->dropColumn('is_sequent');
        });
    }
};
