<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_alerts', function (Blueprint $table) {
            $table->id();
            $table->longText('body_ar')->nullable();
            $table->longText('body_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_alerts');
    }
};
