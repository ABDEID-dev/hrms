<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $leaveTypes = [
        [
            'id' => 1101,
            'name' => 'اجازة يومية',
            'is_instantly' => 0,
            'is_accumulative' => 1,
            'discount_rate' => 100,
            'days_limit' => 0,
            'minutes_limit' => 0,
            'notes' => 'Daily administrative leave',
        ],
        [
            'id' => 1102,
            'name' => 'اجازة رسمية',
            'is_instantly' => 1,
            'is_accumulative' => 0,
            'discount_rate' => 0,
            'days_limit' => 0,
            'minutes_limit' => 0,
            'notes' => 'Official leave without deduction',
        ],
        [
            'id' => 1103,
            'name' => 'اجازة بدون راتب',
            'is_instantly' => 1,
            'is_accumulative' => 0,
            'discount_rate' => 100,
            'days_limit' => 0,
            'minutes_limit' => 0,
            'notes' => 'Unpaid leave',
        ],
        [
            'id' => 1104,
            'name' => 'اجازة مرضية',
            'is_instantly' => 1,
            'is_accumulative' => 0,
            'discount_rate' => 100,
            'days_limit' => 0,
            'minutes_limit' => 0,
            'notes' => 'Sick leave',
        ],
        [
            'id' => 1201,
            'name' => 'إذن ساعات',
            'is_instantly' => 1,
            'is_accumulative' => 1,
            'discount_rate' => 100,
            'days_limit' => 0,
            'minutes_limit' => 0,
            'notes' => 'Hourly permission',
        ],
        [
            'id' => 2101,
            'name' => 'اجازة مهمة عمل',
            'is_instantly' => 1,
            'is_accumulative' => 0,
            'discount_rate' => 0,
            'days_limit' => 0,
            'minutes_limit' => 0,
            'notes' => 'Daily work mission',
        ],
    ];

    public function up(): void
    {
        $now = Carbon::now();

        foreach ($this->leaveTypes as $leaveType) {
            $exists = DB::table('leaves')->where('id', $leaveType['id'])->exists();

            $payload = array_merge($leaveType, [
                'updated_by' => 'System',
                'deleted_by' => null,
                'deleted_at' => null,
                'updated_at' => $now,
            ]);

            if ($exists) {
                DB::table('leaves')
                    ->where('id', $leaveType['id'])
                    ->update($payload);
            } else {
                DB::table('leaves')->insert(array_merge($payload, [
                    'created_by' => 'System',
                    'created_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('leaves')->whereIn('id', array_column($this->leaveTypes, 'id'))->delete();
    }
};
