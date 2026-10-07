<?php

namespace Tests\Feature;

use App\Livewire\Assets\Inventory;
use App\Models\InventoryProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_product_image_is_stored_when_product_is_created(): void
    {
        Storage::fake('public');
        $user = $this->adminUserWithInventoryAccess();
        $this->actingAs($user);

        Livewire::test(Inventory::class)
            ->set('productForm.category', 'hair_care_products')
            ->set('productForm.name', 'Image Upload Product')
            ->set('productForm.stock_quantity', 0)
            ->set('productImage', UploadedFile::fake()->createWithContent(
                'product.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jMioAAAAASUVORK5CYII=', true)
            ))
            ->call('saveProduct')
            ->assertHasNoErrors();

        $product = InventoryProduct::query()->where('name', 'Image Upload Product')->firstOrFail();

        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_inventory_product_image_must_be_an_image(): void
    {
        Storage::fake('public');
        $user = $this->adminUserWithInventoryAccess();
        $this->actingAs($user);

        Livewire::test(Inventory::class)
            ->set('productForm.category', 'hair_care_products')
            ->set('productForm.name', 'Invalid Image Product')
            ->set('productForm.stock_quantity', 0)
            ->set('productImage', UploadedFile::fake()->create('not-an-image.txt', 5, 'text/plain'))
            ->call('saveProduct')
            ->assertHasErrors(['productImage' => 'image']);

        $this->assertDatabaseMissing('inventory_products', ['name' => 'Invalid Image Product']);
    }

    public function test_stock_and_low_stock_values_omit_trailing_decimal_zeroes(): void
    {
        $user = $this->adminUserWithInventoryAccess();
        $this->actingAs($user);

        $pieceProduct = InventoryProduct::query()->create([
            'account' => 'maktoom',
            'category' => 'hair_care_products',
            'name' => 'Piece Quantity Product',
            'unit' => 'piece',
            'stock_quantity' => '12.000',
            'sold_quantity' => 0,
            'low_stock_threshold' => '0.000',
            'is_active' => true,
        ]);

        Livewire::test(Inventory::class)
            ->call('showEditProductModal', $pieceProduct->id)
            ->assertSet('productForm.stock_quantity', '12')
            ->assertSet('productForm.low_stock_threshold', '0');

        $legacyFractionalPieceProduct = InventoryProduct::query()->create([
            'account' => 'maktoom',
            'category' => 'hair_care_products',
            'name' => 'Legacy Fractional Piece Product',
            'unit' => 'piece',
            'stock_quantity' => '0.003',
            'sold_quantity' => 0,
            'low_stock_threshold' => '0.002',
            'is_active' => true,
        ]);

        Livewire::test(Inventory::class)
            ->call('showEditProductModal', $legacyFractionalPieceProduct->id)
            ->assertSet('productForm.stock_quantity', '0')
            ->assertSet('productForm.low_stock_threshold', '0');

        $gramProduct = InventoryProduct::query()->create([
            'account' => 'maktoom',
            'category' => 'iranian_hair',
            'name' => 'Gram Quantity Product',
            'unit' => 'gram',
            'stock_quantity' => '0.125',
            'sold_quantity' => 0,
            'low_stock_threshold' => '0.250',
            'is_active' => true,
        ]);

        Livewire::test(Inventory::class)
            ->call('showEditProductModal', $gramProduct->id)
            ->assertSet('productForm.stock_quantity', '0.125')
            ->assertSet('productForm.low_stock_threshold', '0.25');
    }

    public function test_piece_stock_and_low_stock_values_must_be_whole_numbers(): void
    {
        $user = $this->adminUserWithInventoryAccess();
        $this->actingAs($user);

        Livewire::test(Inventory::class)
            ->set('productForm.category', 'hair_care_products')
            ->set('productForm.name', 'Fractional Piece Product')
            ->set('productForm.stock_quantity', '0.003')
            ->set('productForm.low_stock_threshold', '0.002')
            ->call('saveProduct')
            ->assertHasErrors([
                'productForm.stock_quantity' => 'integer',
                'productForm.low_stock_threshold' => 'integer',
            ]);
    }

    public function test_gram_stock_and_low_stock_values_can_be_fractional(): void
    {
        $user = $this->adminUserWithInventoryAccess();
        $this->actingAs($user);

        Livewire::test(Inventory::class)
            ->set('productForm.category', 'iranian_hair')
            ->set('productForm.name', 'Fractional Gram Product')
            ->set('productForm.stock_quantity', '0.125')
            ->set('productForm.low_stock_threshold', '0.003')
            ->call('saveProduct')
            ->assertHasNoErrors();
    }

    private function adminUserWithInventoryAccess(): User
    {
        $user = User::factory()->make();
        $user->setRelation('roles', collect([
            new Role(['name' => 'Admin', 'guard_name' => 'web']),
        ]));

        Gate::before(fn ($authenticatedUser, $ability) => $authenticatedUser === $user ? true : null);

        return $user;
    }
}
