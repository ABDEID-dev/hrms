<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'display_name')) {
                $table->string('display_name')->nullable()->after('name');
            }

            if (! Schema::hasColumn('roles', 'description')) {
                $table->text('description')->nullable()->after('display_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('roles', 'display_name')) {
                $columns[] = 'display_name';
            }

            if (Schema::hasColumn('roles', 'description')) {
                $columns[] = 'description';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
