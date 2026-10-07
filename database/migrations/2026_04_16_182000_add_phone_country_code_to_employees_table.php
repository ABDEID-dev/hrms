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
            $table->string('phone_country_code', 5)->default('971')->after('national_number');
            $table->dropUnique('employees_mobile_number_unique');
            $table->unique(['phone_country_code', 'mobile_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_phone_country_code_mobile_number_unique');
            $table->dropColumn('phone_country_code');
            $table->unique('mobile_number');
        });
    }
};
