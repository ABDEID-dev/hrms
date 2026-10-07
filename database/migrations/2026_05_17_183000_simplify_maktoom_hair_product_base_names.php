<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('inventory_products')
                ->where('account', 'maktoom')
                ->where('category', 'double_face_hair')
                ->update([
                    'name' => 'شعر دبل فيس',
                    'updated_by' => 'System',
                    'updated_at' => now(),
                ]);

            DB::table('inventory_products')
                ->where('account', 'maktoom')
                ->where('category', 'iranian_hair')
                ->update([
                    'name' => 'شعر ايراني',
                    'updated_by' => 'System',
                    'updated_at' => now(),
                ]);

            DB::table('inventory_products')
                ->where('account', 'maktoom')
                ->where('category', 'indian_hair')
                ->update([
                    'name' => 'شعر هندي',
                    'updated_by' => 'System',
                    'updated_at' => now(),
                ]);
        });
    }

    public function down(): void
    {
        // The simplified base names are kept because later stock movements can reference these products.
    }
};
