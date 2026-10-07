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
        Schema::create('secret_archive_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('secret_archive_folders')->cascadeOnDelete();
            $table->string('path')->unique();
            $table->string('original_name');
            $table->string('mime', 190);
            $table->unsignedBigInteger('size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['folder_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secret_archive_files');
    }
};
