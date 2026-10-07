<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NORMALIZED_NOTE = 'Normalized Maktoom hair bundle names and grouped stock';

    public function up(): void
    {
        DB::transaction(function () {
            foreach ($this->gramHairGroups() as $group) {
                $this->mergeProducts($group);
            }

            foreach ($this->pieceHairProducts() as $product) {
                DB::table('inventory_products')
                    ->where('account', 'maktoom')
                    ->where('name', $product['old_name'])
                    ->update([
                        'category' => 'double_face_hair',
                        'name' => $product['name'],
                        'color' => $product['color'],
                        'length_cm' => $product['length_cm'],
                        'unit' => 'piece',
                        'note' => self::NORMALIZED_NOTE,
                        'updated_by' => 'System',
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    public function down(): void
    {
        // Data normalization is intentionally kept because stock movements may be created after deployment.
    }

    private function mergeProducts(array $group): void
    {
        $products = DB::table('inventory_products')
            ->where('account', 'maktoom')
            ->whereIn('name', $group['old_names'])
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        $target = $this->targetProduct($products, $group);
        $duplicateIds = $products->pluck('id')->reject(fn ($id) => (int) $id === (int) $target->id)->values();

        if ($duplicateIds->isNotEmpty()) {
            DB::table('inventory_movements')
                ->whereIn('inventory_product_id', $duplicateIds)
                ->update(['inventory_product_id' => $target->id]);

            DB::table('account_transactions')
                ->whereIn('inventory_product_id', $duplicateIds)
                ->update(['inventory_product_id' => $target->id]);
        }

        DB::table('inventory_products')
            ->where('id', $target->id)
            ->update([
                'category' => $group['category'],
                'name' => $group['name'],
                'color' => $group['color'],
                'length_cm' => $group['length_cm'],
                'unit' => 'gram',
                'stock_quantity' => $products->sum(fn ($product) => (float) $product->stock_quantity),
                'sold_quantity' => $products->sum(fn ($product) => (float) $product->sold_quantity),
                'note' => self::NORMALIZED_NOTE,
                'updated_by' => 'System',
                'updated_at' => now(),
            ]);

        if ($duplicateIds->isNotEmpty()) {
            DB::table('inventory_products')->whereIn('id', $duplicateIds)->delete();
        }
    }

    private function targetProduct(Collection $products, array $group): object
    {
        $existing = DB::table('inventory_products')
            ->where('account', 'maktoom')
            ->where('name', $group['name'])
            ->first();

        return $existing ?: $products->first();
    }

    private function gramHairGroups(): array
    {
        return [
            [
                'name' => 'شعر ايراني بني 80 سم',
                'category' => 'iranian_hair',
                'color' => 'بني',
                'length_cm' => 80,
                'old_names' => [
                    'ربطة الاولى (خيط بني)',
                    'ربطة السادسة (خيط بوني)',
                ],
            ],
            [
                'name' => 'شعر ايراني احمر 80 سم',
                'category' => 'iranian_hair',
                'color' => 'احمر',
                'length_cm' => 80,
                'old_names' => [
                    'ربطة الثالثة (خيط احمر)',
                    'ربطة الخامسة (خيط احمر)',
                    'ربطة الثاني عشر (خيط احمر)',
                    'ربطة سادس عشر (خيط احمر)',
                    'ربطة العشرون (خيط احمر)',
                ],
            ],
            [
                'name' => 'شعر ايراني وردي 80 سم',
                'category' => 'iranian_hair',
                'color' => 'وردي',
                'length_cm' => 80,
                'old_names' => [
                    'ربطة الرابعة (خيط وردي)',
                    'ربطة التاسعة (خيط وردي)',
                    'ربطة الخامس عشر (خيط وردي)',
                ],
            ],
            [
                'name' => 'شعر ايراني اصفر 80 سم',
                'category' => 'iranian_hair',
                'color' => 'اصفر',
                'length_cm' => 80,
                'old_names' => [
                    'ربطة الثامنة (خيط اصفر)',
                    'ربطة العاشرة (خيط اصفر)',
                    'ربطة الثامن عشر (خيط اصفر)',
                    'ربطة التاسع عشر (خيط اصفر)',
                ],
            ],
            [
                'name' => 'شعر ايراني رمادي 80 سم',
                'category' => 'iranian_hair',
                'color' => 'رمادي',
                'length_cm' => 80,
                'old_names' => ['ربطة السابعة (خيط رمادي)'],
            ],
            [
                'name' => 'شعر ايراني بيج 80 سم',
                'category' => 'iranian_hair',
                'color' => 'بيج',
                'length_cm' => 80,
                'old_names' => ['ربطة الرابع عشر (خيط بيج)'],
            ],
            [
                'name' => 'شعر ايراني اخضر 80 سم',
                'category' => 'iranian_hair',
                'color' => 'اخضر',
                'length_cm' => 80,
                'old_names' => ['ربطة السابع عشر (خيط اخضر)'],
            ],
            [
                'name' => 'شعر ايراني ابيض 80 سم',
                'category' => 'iranian_hair',
                'color' => 'ابيض',
                'length_cm' => 80,
                'old_names' => ['ربطة الثانية وعشرون (افير ابيض)'],
            ],
            [
                'name' => 'شعر ايراني فاتح 80 سم',
                'category' => 'iranian_hair',
                'color' => 'فاتح',
                'length_cm' => 80,
                'old_names' => [
                    'ربطة السابع والعشرون (شعر العرض الفاتح)',
                    'شعر الايراني (فاتح)',
                ],
            ],
            [
                'name' => 'شعر ايراني طبيعي 80 سم',
                'category' => 'iranian_hair',
                'color' => 'طبيعي',
                'length_cm' => 80,
                'old_names' => [
                    'ربطة الثانية (بلاستيك)',
                    'ربطة الحادي عشر (هذا الشعر مع محمد)',
                    'ربطة الثالث عشر',
                    'ربطة واحد والعشرون (هذا الشعر مبنط)',
                    'ربطة الثالثة وعشرون (شطورة قطعة واحدة)',
                    'ربطة الرابعة وعشرون (شعر العرض)',
                    'ربطة الخامسة وعشرون (الشعر ايراني ايضا)',
                    'ربطة السادسة والعشرون (شعر العرض)',
                ],
            ],
            [
                'name' => 'شعر هندي طبيعي 80 سم',
                'category' => 'indian_hair',
                'color' => 'طبيعي',
                'length_cm' => 80,
                'old_names' => ['ربطة الثامنة والعشرون (شعر هندي يتبنط مع ابانوب - قبل تبنيط)'],
            ],
        ];
    }

    private function pieceHairProducts(): array
    {
        return [
            [
                'old_name' => 'شعر الستيكر 4 بوكسات اللون فاتح',
                'name' => 'شعر دبل فيس فاتح 80 سم',
                'color' => 'فاتح',
                'length_cm' => 80,
            ],
            [
                'old_name' => 'شعر الستيكر 6 بوكسات اسود طويل',
                'name' => 'شعر دبل فيس اسود 120 سم',
                'color' => 'اسود',
                'length_cm' => 120,
            ],
            [
                'old_name' => 'شعر الستيكر 3 بوكسات اسود قصير',
                'name' => 'شعر دبل فيس اسود 60 سم',
                'color' => 'اسود',
                'length_cm' => 60,
            ],
            [
                'old_name' => 'شعر الستيكر 2 بوكسات بني فاتح متوسط الطول',
                'name' => 'شعر دبل فيس بني فاتح 80 سم',
                'color' => 'بني فاتح',
                'length_cm' => 80,
            ],
            [
                'old_name' => 'شعر الستيكر 3 بوكسات احمر متوسط الطول',
                'name' => 'شعر دبل فيس احمر 80 سم',
                'color' => 'احمر',
                'length_cm' => 80,
            ],
            [
                'old_name' => 'شعر هيرتوك بوكس اشقر متوسط الطول',
                'name' => 'شعر دبل فيس اشقر 80 سم',
                'color' => 'اشقر',
                'length_cm' => 80,
            ],
            [
                'old_name' => 'متواجد فقط 9 حبات من الستيكر',
                'name' => 'شعر دبل فيس متنوع 80 سم',
                'color' => 'متنوع',
                'length_cm' => 80,
            ],
        ];
    }
};
