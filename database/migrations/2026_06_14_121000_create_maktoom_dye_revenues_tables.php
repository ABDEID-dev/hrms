<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maktoom_dye_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->date('date')->index();
            $table->unsignedInteger('total_quantity')->default(0);
            $table->text('note')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maktoom_dye_revenue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maktoom_dye_revenue_id')
                ->constrained('maktoom_dye_revenues')
                ->cascadeOnDelete();
            $table->foreignId('maktoom_dye_id')
                ->constrained('maktoom_dyes')
                ->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['maktoom_dye_revenue_id', 'maktoom_dye_id'],
                'dye_revenue_dye_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maktoom_dye_revenue_items');
        Schema::dropIfExists('maktoom_dye_revenues');
    }
};
