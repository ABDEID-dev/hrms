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
                ->whereIn('category', ['iranian_hair', 'indian_hair'])
                ->update([
                    'unit_price' => DB::raw("
                        case
                            when length_cm = 60 then 10
                            when length_cm = 80 then 12
                            when length_cm = 100 then 13
                            when length_cm = 120 then 14
                            else 12
                        end
                    "),
                    'updated_by' => 'System',
                    'updated_at' => now(),
                ]);

            DB::table('inventory_products')
                ->where('account', 'maktoom')
                ->where('category', 'double_face_hair')
                ->update([
                    'unit_price' => DB::raw("
                        case
                            when length_cm = 60 then 150
                            when length_cm = 80 then 180
                            when length_cm = 100 then 200
                            when length_cm = 120 then 220
                            else 180
                        end
                    "),
                    'updated_by' => 'System',
                    'updated_at' => now(),
                ]);

            foreach ($this->hairCarePrices() as $keyword => $price) {
                DB::table('inventory_products')
                    ->where('account', 'maktoom')
                    ->where('category', 'hair_care_products')
                    ->where('name', 'like', '%'.$keyword.'%')
                    ->update([
                        'unit_price' => $price,
                        'updated_by' => 'System',
                        'updated_at' => now(),
                    ]);
            }

            DB::table('inventory_products')
                ->where('account', 'maktoom')
                ->where('category', 'hair_care_products')
                ->whereNull('unit_price')
                ->update([
                    'unit_price' => 50,
                    'updated_by' => 'System',
                    'updated_at' => now(),
                ]);
        });
    }

    public function down(): void
    {
        // Trial prices are intentionally kept because sales may be created after deployment.
    }

    private function hairCarePrices(): array
    {
        return [
            'Hair Care Mask' => 65,
            'Treatment' => 70,
            'Shampoo Bar' => 35,
            'Shampoo' => 55,
            'Conditioner' => 60,
            'Conditiner' => 60,
            'Protector' => 65,
            'Proctctor' => 65,
            'Protctor' => 35,
            'Oil' => 75,
            'Surme' => 60,
            'Serum' => 60,
            'Vilot Lotion' => 45,
            'Detox Gel' => 45,
            'Recover' => 80,
            'Hyaluronic' => 90,
            'Hair Conditioner Re' => 45,
            'Shampoo Re' => 45,
            'Facial Cleanser' => 40,
            'Rutin Serum' => 55,
            'Hand Cream' => 25,
            'Oxidant' => 30,
            'i.plex' => 95,
            'Olaplex' => 120,
        ];
    }
};
