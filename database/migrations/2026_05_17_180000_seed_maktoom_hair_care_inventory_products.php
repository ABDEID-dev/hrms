<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $products = [
            ['Hair Care Mask Saffron copper 250ml Teknia', 3],
            ['Hair Care Mask Cocoa Brown 250ml Teknia', 2],
            ['Hair Care Mask White Silver 250 ml Teknia', 2],
            ['Hair Care Mask Coral Red 250 ml Teknia', 2],
            ['Hair Care Mask Violet Lavender 250 ml Teknia', 3],
            ['Proctctor Frizz Control 300ml Teknia', 3],
            ['ProctctorBody Maker 300ml Teknia', 3],
            ['Shampoo White Silver 300 ml Teknia', 4],
            ['Shampoo Color Stay 300ml Teknia', 3],
            ['Shampoo Body Maker 300ml Teknia', 3],
            ['ShampooDetox 300ml Teknia', 3],
            ['Shampoo Violet Lavender 300 ml Teknia', 3],
            ['Shampoo Coral Red 300 ml Teknia', 3],
            ['Shampoo Saffron Copper 300 ml Teknia', 3],
            ['Shampoo Coca Brown 300 ml Teknia', 2],
            ['Shampoo Frizz Control 300 ml Teknia', 2],
            ['Shampoo Pure 300 ml Teknia', 2],
            ['Shampoo Full Defense 300 ml Teknia', 3],
            ['Shampoo DEEP Care 300 ml Teknia', 2],
            ['Shampoo Perfect Cleanse 300 ml Teknia', 1],
            ['Shampoo Vital 300 ml Teknia', 1],
            ['Shampoo Organic Balance 300 ml Teknia', 1],
            ['Shampoo Relief 300 ml Teknia', 1],
            ['Conditiner Frizz Control 300ml Teknia', 3],
            ['Conditioner Deep Care 300 ml Teknia', 3],
            ['Conditioner Color Stay 300 ml Teknia', 3],
            ['Conditioner Body Maker 300 ml Teknia', 3],
            ['Treatment Frizz Control 250 ml Teknia', 3],
            ['Treatment Full Defense 250 ml Teknia', 3],
            ['Treatment Color Stay 250 ml Teknia', 2],
            ['Treatment Argan Oil 250 ml Teknia', 2],
            ['Treatment Deep Care 250 ml Teknia', 1],
            ['Treatment Pure 250 ml Teknia', 3],
            ['Treatment Organic Balance 250 ml Teknia', 3],
            ['Treatment Relief 250 ml Teknia', 2],
            ['Shampoo Bar Argan Oil 80g Teknia', 2],
            ['Oil Organic Balance 100 ml Teknia', 3],
            ['Oil Argain 125 ml Teknia', 3],
            ['Surme Full Defense 100 ml Teknia', 3],
            ['Surme Deep 100 ml Teknia', 2],
            ['Vilot Lotion 150ml Teknia', 2],
            ['Detox Gel 150ml Teknia', 3],
            ['Recover K.20 250ml Lakme', 2],
            ['Conditioner K.20 300ml Lakme', 4],
            ['Hyaluronic Treatment 100 ml Lakme', 6],
            ['Protctor oil K.20 30ml Lakme', 4],
            ['Hair Conditioner Re 500ml', 9],
            ['Shampoo Re 500ml', 5],
            ['Facial Cleanser', 12],
            ['Rutin Serum+Vitamen C', 30],
            ['Hand Cream Re', 13],
            ['Oxidant 18V Lakme 1000 ml', 18],
            ['Oxidant 9V Lakme 1000 ml', 2],
            ['Oxidant 28V Lakme 1000 ml', 7],
            ['i.plex Lakme 500 ml', 2],
            ['Olaplex 525 ml', 1],
            ['Hair Care Whits Silver Teknia', 1],
        ];

        foreach ($products as [$name, $quantity]) {
            $exists = DB::table('inventory_products')
                ->where('account', 'maktoom')
                ->where('name', $name)
                ->exists();

            if ($exists) {
                continue;
            }

            $productId = DB::table('inventory_products')->insertGetId([
                'account' => 'maktoom',
                'category' => 'hair_care_products',
                'name' => $name,
                'unit' => 'piece',
                'stock_quantity' => $quantity,
                'sold_quantity' => 0,
                'is_active' => true,
                'note' => 'Seeded from initial Maktoom inventory list',
                'created_by' => 'System',
                'updated_by' => 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('inventory_movements')->insert([
                'inventory_product_id' => $productId,
                'account' => 'maktoom',
                'type' => 'purchase',
                'quantity' => $quantity,
                'balance_after' => $quantity,
                'occurred_at' => now(),
                'note' => 'Opening stock',
                'created_by' => 'System',
                'updated_by' => 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('inventory_products')
            ->where('account', 'maktoom')
            ->whereIn('category', ['الشعر الايراني', 'الشعر الإيراني', 'شعر ايراني', 'شعر إيراني'])
            ->update([
                'category' => 'iranian_hair',
                'unit' => 'gram',
                'updated_by' => 'System',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $names = [
            'Hair Care Mask Saffron copper 250ml Teknia',
            'Hair Care Mask Cocoa Brown 250ml Teknia',
            'Hair Care Mask White Silver 250 ml Teknia',
            'Hair Care Mask Coral Red 250 ml Teknia',
            'Hair Care Mask Violet Lavender 250 ml Teknia',
            'Proctctor Frizz Control 300ml Teknia',
            'ProctctorBody Maker 300ml Teknia',
            'Shampoo White Silver 300 ml Teknia',
            'Shampoo Color Stay 300ml Teknia',
            'Shampoo Body Maker 300ml Teknia',
            'ShampooDetox 300ml Teknia',
            'Shampoo Violet Lavender 300 ml Teknia',
            'Shampoo Coral Red 300 ml Teknia',
            'Shampoo Saffron Copper 300 ml Teknia',
            'Shampoo Coca Brown 300 ml Teknia',
            'Shampoo Frizz Control 300 ml Teknia',
            'Shampoo Pure 300 ml Teknia',
            'Shampoo Full Defense 300 ml Teknia',
            'Shampoo DEEP Care 300 ml Teknia',
            'Shampoo Perfect Cleanse 300 ml Teknia',
            'Shampoo Vital 300 ml Teknia',
            'Shampoo Organic Balance 300 ml Teknia',
            'Shampoo Relief 300 ml Teknia',
            'Conditiner Frizz Control 300ml Teknia',
            'Conditioner Deep Care 300 ml Teknia',
            'Conditioner Color Stay 300 ml Teknia',
            'Conditioner Body Maker 300 ml Teknia',
            'Treatment Frizz Control 250 ml Teknia',
            'Treatment Full Defense 250 ml Teknia',
            'Treatment Color Stay 250 ml Teknia',
            'Treatment Argan Oil 250 ml Teknia',
            'Treatment Deep Care 250 ml Teknia',
            'Treatment Pure 250 ml Teknia',
            'Treatment Organic Balance 250 ml Teknia',
            'Treatment Relief 250 ml Teknia',
            'Shampoo Bar Argan Oil 80g Teknia',
            'Oil Organic Balance 100 ml Teknia',
            'Oil Argain 125 ml Teknia',
            'Surme Full Defense 100 ml Teknia',
            'Surme Deep 100 ml Teknia',
            'Vilot Lotion 150ml Teknia',
            'Detox Gel 150ml Teknia',
            'Recover K.20 250ml Lakme',
            'Conditioner K.20 300ml Lakme',
            'Hyaluronic Treatment 100 ml Lakme',
            'Protctor oil K.20 30ml Lakme',
            'Hair Conditioner Re 500ml',
            'Shampoo Re 500ml',
            'Facial Cleanser',
            'Rutin Serum+Vitamen C',
            'Hand Cream Re',
            'Oxidant 18V Lakme 1000 ml',
            'Oxidant 9V Lakme 1000 ml',
            'Oxidant 28V Lakme 1000 ml',
            'i.plex Lakme 500 ml',
            'Olaplex 525 ml',
            'Hair Care Whits Silver Teknia',
        ];

        $productIds = DB::table('inventory_products')
            ->where('account', 'maktoom')
            ->where('note', 'Seeded from initial Maktoom inventory list')
            ->whereIn('name', $names)
            ->pluck('id');

        DB::table('inventory_movements')->whereIn('inventory_product_id', $productIds)->delete();
        DB::table('inventory_products')->whereIn('id', $productIds)->delete();
    }
};
