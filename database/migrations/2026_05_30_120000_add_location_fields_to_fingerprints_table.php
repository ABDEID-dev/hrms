<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprints', function (Blueprint $table) {
            $table->decimal('system_open_latitude', 10, 7)->nullable()->after('excuse');
            $table->decimal('system_open_longitude', 10, 7)->nullable()->after('system_open_latitude');
            $table->decimal('system_open_accuracy', 10, 2)->nullable()->after('system_open_longitude');
            $table->timestamp('system_open_at')->nullable()->after('system_open_accuracy');
            $table->string('system_open_photo_path')->nullable()->after('system_open_at');

            $table->decimal('check_in_latitude', 10, 7)->nullable()->after('system_open_photo_path');
            $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            $table->decimal('check_in_accuracy', 10, 2)->nullable()->after('check_in_longitude');
            $table->string('check_in_photo_path')->nullable()->after('check_in_accuracy');

            $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_in_photo_path');
            $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            $table->decimal('check_out_accuracy', 10, 2)->nullable()->after('check_out_longitude');
            $table->string('check_out_photo_path')->nullable()->after('check_out_accuracy');
        });
    }

    public function down(): void
    {
        Schema::table('fingerprints', function (Blueprint $table) {
            $table->dropColumn([
                'system_open_latitude',
                'system_open_longitude',
                'system_open_accuracy',
                'system_open_at',
                'system_open_photo_path',
                'check_in_latitude',
                'check_in_longitude',
                'check_in_accuracy',
                'check_in_photo_path',
                'check_out_latitude',
                'check_out_longitude',
                'check_out_accuracy',
                'check_out_photo_path',
            ]);
        });
    }
};
