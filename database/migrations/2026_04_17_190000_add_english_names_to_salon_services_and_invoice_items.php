<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salon_services', function (Blueprint $table) {
            $table->string('english_name')->nullable()->after('name');
        });

        Schema::table('salon_invoice_items', function (Blueprint $table) {
            $table->string('service_name_en')->nullable()->after('service_name');
        });
    }

    public function down(): void
    {
        Schema::table('salon_invoice_items', function (Blueprint $table) {
            $table->dropColumn('service_name_en');
        });

        Schema::table('salon_services', function (Blueprint $table) {
            $table->dropColumn('english_name');
        });
    }
};
