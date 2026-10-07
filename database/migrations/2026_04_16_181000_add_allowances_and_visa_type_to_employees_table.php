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
            $table->decimal('housing_allowance', 10, 2)->nullable()->after('basic_salary');
            $table->decimal('transportation_allowance', 10, 2)->nullable()->after('housing_allowance');
            $table->string('visa_type')->nullable()->after('transportation_allowance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['housing_allowance', 'transportation_allowance', 'visa_type']);
        });
    }
};
