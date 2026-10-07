<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maktoom_dyes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('warehouse_stock')->default(0);
            $table->unsignedInteger('shop_stock')->default(0);
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'name']);
        });

        Schema::create('maktoom_dye_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maktoom_dye_id')->constrained('maktoom_dyes')->cascadeOnDelete();
            $table->enum('type', [
                'purchase',
                'transfer_to_shop',
                'sale_from_warehouse',
                'sale_from_shop',
                'warehouse_adjustment',
                'shop_adjustment',
            ]);
            $table->integer('quantity');
            $table->unsignedInteger('warehouse_balance_after')->default(0);
            $table->unsignedInteger('shop_balance_after')->default(0);
            $table->unsignedInteger('sold_quantity_after')->default(0);
            $table->timestamp('occurred_at')->index();
            $table->text('note')->nullable();
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['maktoom_dye_id', 'type']);
        });

        $now = now();
        $dyes = [
            ['code' => '1', 'name' => '10/00', 'warehouse_stock' => 11],
            ['code' => '2', 'name' => '9/00', 'warehouse_stock' => 8],
            ['code' => '3', 'name' => '99/00', 'warehouse_stock' => 4],
            ['code' => '4', 'name' => '88/00', 'warehouse_stock' => 9],
            ['code' => '5', 'name' => '77/00', 'warehouse_stock' => 8],
            ['code' => '6', 'name' => '7/00', 'warehouse_stock' => 7],
            ['code' => '7', 'name' => '07/06', 'warehouse_stock' => 6],
        ];

        foreach ($dyes as $dye) {
            $id = DB::table('maktoom_dyes')->insertGetId($dye + [
                'shop_stock' => 0,
                'sold_quantity' => 0,
                'is_active' => true,
                'created_by' => 'System',
                'updated_by' => 'System',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('maktoom_dye_movements')->insert([
                'maktoom_dye_id' => $id,
                'type' => 'purchase',
                'quantity' => $dye['warehouse_stock'],
                'warehouse_balance_after' => $dye['warehouse_stock'],
                'shop_balance_after' => 0,
                'sold_quantity_after' => 0,
                'occurred_at' => $now,
                'note' => 'Opening stock from dye sheet',
                'created_by' => 'System',
                'updated_by' => 'System',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maktoom_dye_movements');
        Schema::dropIfExists('maktoom_dyes');
    }
};
