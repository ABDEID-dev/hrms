<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dyes = [
            ['code' => '1', 'name' => '10/00', 'stock' => 11, 'note' => null],
            ['code' => '2', 'name' => '9/00', 'stock' => 8, 'note' => null],
            ['code' => '3', 'name' => '99/00', 'stock' => 4, 'note' => null],
            ['code' => '4', 'name' => '88/00', 'stock' => 9, 'note' => null],
            ['code' => '5', 'name' => '77/00', 'stock' => 8, 'note' => null],
            ['code' => '6', 'name' => '7/00', 'stock' => 7, 'note' => null],
            ['code' => '7', 'name' => '07/06', 'stock' => 6, 'note' => null],
            ['code' => '8', 'name' => '5/00', 'stock' => 1, 'note' => 'استعملت من طرف محمد سمعة ودفعت الزبونة 200'],
            ['code' => '9', 'name' => '6/00', 'stock' => 2, 'note' => null],
            ['code' => '10', 'name' => '55/00', 'stock' => 1, 'note' => null],
            ['code' => '11', 'name' => '05/06', 'stock' => 6, 'note' => null],
            ['code' => '12', 'name' => '09/06', 'stock' => 6, 'note' => null],
            ['code' => '13', 'name' => '66/00', 'stock' => 10, 'note' => null],
            ['code' => '14', 'name' => '8/00', 'stock' => 12, 'note' => null],
            ['code' => '15', 'name' => '08/06', 'stock' => 6, 'note' => null],
            ['code' => '16', 'name' => '0/10', 'stock' => 1, 'note' => null],
            ['code' => '17', 'name' => '0/00', 'stock' => 1, 'note' => null],
            ['code' => '18', 'name' => '0/20', 'stock' => 1, 'note' => null],
            ['code' => '19', 'name' => '0/02', 'stock' => 2, 'note' => null],
            ['code' => '20', 'name' => '0/50', 'stock' => 2, 'note' => null],
            ['code' => '21', 'name' => '0/70', 'stock' => 3, 'note' => null],
            ['code' => '22', 'name' => '0/07', 'stock' => 2, 'note' => null],
            ['code' => '23', 'name' => '12/72', 'stock' => 6, 'note' => null],
            ['code' => '24', 'name' => '12/00', 'stock' => 4, 'note' => null],
            ['code' => '25', 'name' => '12/10', 'stock' => 2, 'note' => 'استعملو من طرف محمد'],
            ['code' => '26', 'name' => '12/20', 'stock' => 6, 'note' => null],
            ['code' => '27', 'name' => '12/17', 'stock' => 3, 'note' => 'استعملت فقط وحدة ظلو 2'],
            ['code' => '28', 'name' => '12/30', 'stock' => 3, 'note' => null],
            ['code' => '29', 'name' => '12/63', 'stock' => 6, 'note' => null],
            ['code' => '30', 'name' => '1/70', 'stock' => 11, 'note' => null],
            ['code' => '31', 'name' => '10/17', 'stock' => 8, 'note' => null],
            ['code' => '32', 'name' => '10/21', 'stock' => 4, 'note' => null],
            ['code' => '33', 'name' => '10/30', 'stock' => 4, 'note' => null],
            ['code' => '34', 'name' => '10/40', 'stock' => 2, 'note' => null],
            ['code' => '35', 'name' => '9/21', 'stock' => 2, 'note' => null],
            ['code' => '36', 'name' => '9/22', 'stock' => 9, 'note' => null],
            ['code' => '37', 'name' => '9/30', 'stock' => 3, 'note' => null],
            ['code' => '38', 'name' => '8/30', 'stock' => 4, 'note' => null],
            ['code' => '39', 'name' => '8/40', 'stock' => 4, 'note' => null],
            ['code' => '40', 'name' => '8/44', 'stock' => 3, 'note' => null],
            ['code' => '41', 'name' => '8/17', 'stock' => 4, 'note' => null],
            ['code' => '42', 'name' => '8/13', 'stock' => 5, 'note' => null],
            ['code' => '43', 'name' => '8/12', 'stock' => 1, 'note' => null],
            ['code' => '44', 'name' => '7/12', 'stock' => 1, 'note' => 'ايتعمات من طرف محمد'],
            ['code' => '45', 'name' => '7/13', 'stock' => 1, 'note' => 'ايتعمات من طرف محمد'],
            ['code' => '46', 'name' => '7/30', 'stock' => 2, 'note' => null],
            ['code' => '47', 'name' => '7/50', 'stock' => 2, 'note' => null],
            ['code' => '48', 'name' => '7/65', 'stock' => 4, 'note' => null],
            ['code' => '49', 'name' => '7/66', 'stock' => 1, 'note' => null],
            ['code' => '50', 'name' => '7/44', 'stock' => 4, 'note' => null],
            ['code' => '51', 'name' => '6/95', 'stock' => 4, 'note' => null],
            ['code' => '52', 'name' => '6/99', 'stock' => 1, 'note' => null],
            ['code' => '53', 'name' => '6/40', 'stock' => 2, 'note' => null],
            ['code' => '54', 'name' => '6/12', 'stock' => 1, 'note' => null],
            ['code' => '55', 'name' => '6/55', 'stock' => 5, 'note' => null],
            ['code' => '56', 'name' => '6/65', 'stock' => 4, 'note' => null],
            ['code' => '57', 'name' => '6/59', 'stock' => 4, 'note' => null],
            ['code' => '58', 'name' => '6/30', 'stock' => 6, 'note' => null],
            ['code' => '59', 'name' => '5/30', 'stock' => 6, 'note' => null],
            ['code' => '60', 'name' => '5/22', 'stock' => 4, 'note' => null],
            ['code' => '61', 'name' => '5/59', 'stock' => 5, 'note' => null],
            ['code' => '62', 'name' => '5/44', 'stock' => 3, 'note' => null],
            ['code' => '63', 'name' => '5/55', 'stock' => 4, 'note' => null],
            ['code' => '64', 'name' => '5/50', 'stock' => 4, 'note' => null],
            ['code' => '65', 'name' => '4/50', 'stock' => 6, 'note' => null],
            ['code' => '66', 'name' => '3/22', 'stock' => 3, 'note' => null],
            ['code' => '67', 'name' => '8.45', 'stock' => 1, 'note' => null],
            ['code' => '68', 'name' => '7.43', 'stock' => 1, 'note' => null],
            ['code' => '69', 'name' => '7/13', 'stock' => 3, 'note' => null],
            ['code' => '70', 'name' => '5/13', 'stock' => 3, 'note' => null],
            ['code' => '71', 'name' => '6/00', 'stock' => 6, 'note' => 'استعملت وحدة ظلو 5'],
            ['code' => '72', 'name' => '3/00', 'stock' => 4, 'note' => null],
            ['code' => '73', 'name' => '4/00', 'stock' => 3, 'note' => null],
            ['code' => '74', 'name' => '5/00', 'stock' => 3, 'note' => null],
        ];

        DB::transaction(function () use ($dyes) {
            $now = now();

            foreach ($dyes as $row) {
                $existing = DB::table('maktoom_dyes')
                    ->where('code', $row['code'])
                    ->first();

                if ($existing) {
                    $oldStock = (int) $existing->warehouse_stock;
                    $stockDifference = $row['stock'] - $oldStock;

                    DB::table('maktoom_dyes')
                        ->where('id', $existing->id)
                        ->update([
                            'name' => $row['name'],
                            'warehouse_stock' => $row['stock'],
                            'note' => $row['note'],
                            'is_active' => true,
                            'deleted_at' => null,
                            'deleted_by' => null,
                            'updated_by' => 'System',
                            'updated_at' => $now,
                        ]);

                    if ($stockDifference !== 0) {
                        $this->insertMovement(
                            $existing->id,
                            'warehouse_adjustment',
                            $stockDifference,
                            $row['stock'],
                            $now,
                            'Stock synchronized from full dye inventory sheet'
                        );
                    }

                    continue;
                }

                $id = DB::table('maktoom_dyes')->insertGetId([
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'warehouse_stock' => $row['stock'],
                    'shop_stock' => 0,
                    'sold_quantity' => 0,
                    'low_stock_threshold' => null,
                    'is_active' => true,
                    'note' => $row['note'],
                    'created_by' => 'System',
                    'updated_by' => 'System',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($row['stock'] > 0) {
                    $this->insertMovement(
                        $id,
                        'purchase',
                        $row['stock'],
                        $row['stock'],
                        $now,
                        'Opening stock from full dye inventory sheet'
                    );
                }
            }
        });
    }

    public function down(): void
    {
        // Inventory data may receive live movements after import, so rollback is intentionally non-destructive.
    }

    private function insertMovement(
        int $dyeId,
        string $type,
        int $quantity,
        int $balanceAfter,
        $occurredAt,
        string $note
    ): void {
        DB::table('maktoom_dye_movements')->insert([
            'maktoom_dye_id' => $dyeId,
            'type' => $type,
            'quantity' => $quantity,
            'warehouse_balance_after' => $balanceAfter,
            'shop_balance_after' => 0,
            'sold_quantity_after' => 0,
            'occurred_at' => $occurredAt,
            'note' => $note,
            'created_by' => 'System',
            'updated_by' => 'System',
            'created_at' => $occurredAt,
            'updated_at' => $occurredAt,
        ]);
    }
};
