<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SEED_NOTE = 'Seeded from Maktoom hair bundle and sticker list';

    public function up(): void
    {
        $items = [
            ['ربطة الاولى (خيط بني)', 96, 'iranian_hair', 'gram', null, null],
            ['ربطة الثانية (بلاستيك)', 170, 'iranian_hair', 'gram', null, null],
            ['ربطة الثالثة (خيط احمر)', 228, 'iranian_hair', 'gram', null, null],
            ['ربطة الرابعة (خيط وردي)', 145, 'iranian_hair', 'gram', null, null],
            ['ربطة الخامسة (خيط احمر)', 97, 'iranian_hair', 'gram', null, null],
            ['ربطة السادسة (خيط بوني)', 166, 'iranian_hair', 'gram', null, null],
            ['ربطة السابعة (خيط رمادي)', 264, 'iranian_hair', 'gram', null, null],
            ['ربطة الثامنة (خيط اصفر)', 148, 'iranian_hair', 'gram', null, null],
            ['ربطة التاسعة (خيط وردي)', 128, 'iranian_hair', 'gram', null, null],
            ['ربطة العاشرة (خيط اصفر)', 107, 'iranian_hair', 'gram', null, null],
            ['ربطة الحادي عشر (هذا الشعر مع محمد)', 130, 'iranian_hair', 'gram', null, null],
            ['ربطة الثاني عشر (خيط احمر)', 149, 'iranian_hair', 'gram', null, null],
            ['ربطة الثالث عشر', 73, 'iranian_hair', 'gram', null, null],
            ['ربطة الرابع عشر (خيط بيج)', 125, 'iranian_hair', 'gram', null, null],
            ['ربطة الخامس عشر (خيط وردي)', 40, 'iranian_hair', 'gram', null, null],
            ['ربطة سادس عشر (خيط احمر)', 56, 'iranian_hair', 'gram', null, null],
            ['ربطة السابع عشر (خيط اخضر)', 74, 'iranian_hair', 'gram', null, null],
            ['ربطة الثامن عشر (خيط اصفر)', 137, 'iranian_hair', 'gram', null, null],
            ['ربطة التاسع عشر (خيط اصفر)', 191, 'iranian_hair', 'gram', null, null],
            ['ربطة العشرون (خيط احمر)', 136, 'iranian_hair', 'gram', null, null],
            ['ربطة واحد والعشرون (هذا الشعر مبنط)', 75, 'iranian_hair', 'gram', null, null],
            ['ربطة الثانية وعشرون (افير ابيض)', 194, 'iranian_hair', 'gram', null, null],
            ['ربطة الثالثة وعشرون (شطورة قطعة واحدة)', 100, 'iranian_hair', 'gram', null, null],
            ['ربطة الرابعة وعشرون (شعر العرض)', 120, 'iranian_hair', 'gram', null, null],
            ['ربطة الخامسة وعشرون (الشعر ايراني ايضا)', 90, 'iranian_hair', 'gram', null, null],
            ['ربطة السادسة والعشرون (شعر العرض)', 128, 'iranian_hair', 'gram', null, null],
            ['ربطة السابع والعشرون (شعر العرض الفاتح)', 49, 'iranian_hair', 'gram', 'فاتح', null],
            ['ربطة الثامنة والعشرون (شعر هندي يتبنط مع ابانوب - قبل تبنيط)', 74, 'indian_hair', 'gram', null, null],
            ['شعر الايراني (فاتح)', 10, 'iranian_hair', 'gram', 'فاتح', null],
            ['شعر الستيكر 4 بوكسات اللون فاتح', 4, 'double_face_hair', 'piece', 'فاتح', null],
            ['شعر الستيكر 6 بوكسات اسود طويل', 6, 'double_face_hair', 'piece', 'اسود', 120],
            ['شعر الستيكر 3 بوكسات اسود قصير', 3, 'double_face_hair', 'piece', 'اسود', 60],
            ['شعر الستيكر 2 بوكسات بني فاتح متوسط الطول', 2, 'double_face_hair', 'piece', 'بني فاتح', 80],
            ['شعر الستيكر 3 بوكسات احمر متوسط الطول', 3, 'double_face_hair', 'piece', 'احمر', 80],
            ['شعر هيرتوك بوكس اشقر متوسط الطول', 1, 'double_face_hair', 'piece', 'اشقر', 80],
            ['متواجد فقط 9 حبات من الستيكر', 9, 'double_face_hair', 'piece', null, null],
        ];

        foreach ($items as [$name, $quantity, $category, $unit, $color, $lengthCm]) {
            $exists = DB::table('inventory_products')
                ->where('account', 'maktoom')
                ->where('name', $name)
                ->exists();

            if ($exists) {
                continue;
            }

            $productId = DB::table('inventory_products')->insertGetId([
                'account' => 'maktoom',
                'category' => $category,
                'name' => $name,
                'color' => $color,
                'length_cm' => $lengthCm,
                'unit' => $unit,
                'stock_quantity' => $quantity,
                'sold_quantity' => 0,
                'is_active' => true,
                'note' => self::SEED_NOTE,
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
    }

    public function down(): void
    {
        $productIds = DB::table('inventory_products')
            ->where('account', 'maktoom')
            ->where('note', self::SEED_NOTE)
            ->pluck('id');

        DB::table('inventory_movements')->whereIn('inventory_product_id', $productIds)->delete();
        DB::table('inventory_products')->whereIn('id', $productIds)->delete();
    }
};
