<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_requests', 'complainant_name')) {
                $table->string('complainant_name')->nullable()->after('type');
            }

            if (! Schema::hasColumn('employee_requests', 'complaint_against_employee_id')) {
                $table->foreignId('complaint_against_employee_id')
                    ->nullable()
                    ->after('complainant_name')
                    ->constrained('employees')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('employee_requests', 'complaint_against_other')) {
                $table->string('complaint_against_other')->nullable()->after('complaint_against_employee_id');
            }

            if (! Schema::hasColumn('employee_requests', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('amount');
            }

            if (! Schema::hasColumn('employee_requests', 'attachment_original_name')) {
                $table->string('attachment_original_name')->nullable()->after('attachment_path');
            }

            if (! Schema::hasColumn('employee_requests', 'attachment_mime')) {
                $table->string('attachment_mime')->nullable()->after('attachment_original_name');
            }

            if (! Schema::hasColumn('employee_requests', 'voice_path')) {
                $table->string('voice_path')->nullable()->after('attachment_mime');
            }

            if (! Schema::hasColumn('employee_requests', 'voice_mime')) {
                $table->string('voice_mime')->nullable()->after('voice_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_requests', function (Blueprint $table) {
            if (Schema::hasColumn('employee_requests', 'complaint_against_employee_id')) {
                $table->dropForeign(['complaint_against_employee_id']);
                $table->dropColumn('complaint_against_employee_id');
            }
        });

        Schema::table('employee_requests', function (Blueprint $table) {
            $columns = [
                'complainant_name',
                'complaint_against_other',
                'attachment_path',
                'attachment_original_name',
                'attachment_mime',
                'voice_path',
                'voice_mime',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('employee_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
