<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_request_documents')) {
            return;
        }

        Schema::create('employee_request_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind')->default('attachment');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamp('removed_from_complaint_at')->nullable();
            $table->string('removed_from_complaint_by')->nullable();
            $table->timestamp('deleted_from_system_at')->nullable();
            $table->string('deleted_from_system_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_request_documents');
    }
};
