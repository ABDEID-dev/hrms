<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->nullable();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salon_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('branch');
            $table->decimal('service_price', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'machine']);
            $table->boolean('has_warranty')->default(false);
            $table->text('warranty_note')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('invoice_date')->nullable();
            $table->boolean('is_whatsapp_sent')->default(false);
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->string('whatsapp_error')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_invoices');
    }
};
