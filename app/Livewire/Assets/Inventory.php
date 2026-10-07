<?php

namespace App\Livewire\Assets;

use App\Models\InventoryMovement;
use App\Models\InventoryProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Inventory extends Component
{
    use WithFileUploads, WithPagination;

    public string $account = 'maktoom';

    public string $search = '';

    public ?int $editingProductId = null;

    public ?int $stockProductId = null;

    public ?int $confirmedProductId = null;

    public $productImage;

    public ?string $editingProductImagePath = null;

    public array $productForm = [
        'category' => '',
        'name' => '',
        'sku' => '',
        'color' => '',
        'length_cm' => null,
        'unit' => 'piece',
        'stock_quantity' => 0,
        'unit_price' => null,
        'low_stock_threshold' => null,
        'is_active' => true,
        'note' => '',
    ];

    public array $stockForm = [
        'type' => 'purchase',
        'quantity' => null,
        'unit_price' => null,
        'note' => '',
    ];

    private array $accountNames = [
        'maktoom' => 'Maktoum',
        'avani' => 'Avani',
        'perfumes' => 'Perfumes',
    ];

    public array $hairLengths = [60, 80, 100, 120];

    public array $categoryOptions = [
        'double_face_hair' => [
            'label' => 'ui.inventory_category_double_face_hair',
            'unit' => 'piece',
            'has_hair_attributes' => true,
        ],
        'iranian_hair' => [
            'label' => 'ui.inventory_category_iranian_hair',
            'unit' => 'gram',
            'has_hair_attributes' => true,
        ],
        'indian_hair' => [
            'label' => 'ui.inventory_category_indian_hair',
            'unit' => 'gram',
            'has_hair_attributes' => true,
        ],
        'hair_care_products' => [
            'label' => 'ui.inventory_category_hair_care_products',
            'unit' => 'piece',
            'has_hair_attributes' => false,
        ],
        'avani' => [
            'label' => 'ui.inventory_category_avani',
            'unit' => 'piece',
            'has_hair_attributes' => false,
        ],
        'perfumes' => [
            'label' => 'ui.inventory_category_perfumes',
            'unit' => 'piece',
            'has_hair_attributes' => false,
        ],
        'other' => [
            'label' => 'ui.inventory_category_other',
            'unit' => 'piece',
            'has_hair_attributes' => false,
        ],
    ];

    private array $accountCategoryKeys = [
        'maktoom' => [
            'double_face_hair',
            'iranian_hair',
            'indian_hair',
            'hair_care_products',
            'other',
        ],
        'avani' => [
            'double_face_hair',
            'iranian_hair',
            'indian_hair',
            'hair_care_products',
            'avani',
            'other',
        ],
        'perfumes' => [
            'perfumes',
        ],
    ];

    public function mount(): void
    {
        $allowedAccounts = $this->allowedAccountNames();

        abort_if($allowedAccounts === [], 403);

        if (! array_key_exists($this->account, $allowedAccounts)) {
            $this->account = array_key_first($allowedAccounts);
        }
    }

    public function updatingAccount(string $account): void
    {
        abort_unless(array_key_exists($account, $this->allowedAccountNames()), 403);
        $this->resetPage();
    }

    public function updatedAccount(): void
    {
        $this->search = '';
        $this->editingProductId = null;
        $this->stockProductId = null;
        $this->confirmedProductId = null;
        $this->resetProductForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $visibleCategoryOptions = $this->visibleCategoryOptions();
        $visibleCategoryKeys = array_keys($visibleCategoryOptions);

        $products = InventoryProduct::query()
            ->withCount('movements')
            ->where('account', $this->account)
            ->whereIn('category', $visibleCategoryKeys)
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('category', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%')
                        ->orWhere('color', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(10);

        $summary = InventoryProduct::query()
            ->where('account', $this->account)
            ->whereIn('category', $visibleCategoryKeys)
            ->selectRaw('count(*) as products_count')
            ->selectRaw("sum(case when unit = 'piece' then stock_quantity else 0 end) as pieces_stock")
            ->selectRaw("sum(case when unit = 'piece' then sold_quantity else 0 end) as pieces_sold")
            ->selectRaw("sum(case when unit = 'gram' then stock_quantity else 0 end) as grams_stock")
            ->selectRaw("sum(case when unit = 'gram' then sold_quantity else 0 end) as grams_sold")
            ->first();

        $categorySummaryRows = InventoryProduct::query()
            ->where('account', $this->account)
            ->whereIn('category', $visibleCategoryKeys)
            ->select('category', 'unit')
            ->selectRaw('count(*) as products_count')
            ->selectRaw('sum(stock_quantity) as stock_quantity')
            ->selectRaw('sum(sold_quantity) as sold_quantity')
            ->groupBy('category', 'unit')
            ->get()
            ->keyBy('category');

        $categorySummaries = collect($visibleCategoryOptions)
            ->map(function ($option, $category) use ($categorySummaryRows) {
                $row = $categorySummaryRows->get($category);
                $unit = $row?->unit ?: $option['unit'];

                return [
                    'category' => $category,
                    'label' => __($option['label']),
                    'unit' => $unit,
                    'products_count' => (int) ($row?->products_count ?? 0),
                    'stock_quantity' => (float) ($row?->stock_quantity ?? 0),
                    'sold_quantity' => (float) ($row?->sold_quantity ?? 0),
                ];
            })
            ->values();

        $movements = InventoryMovement::query()
            ->with('product')
            ->where('account', $this->account)
            ->whereHas('product', fn ($query) => $query->whereIn('category', $visibleCategoryKeys))
            ->latest('occurred_at')
            ->latest('id')
            ->take(15)
            ->get();

        return view('livewire.assets.inventory', [
            'products' => $products,
            'summary' => $summary,
            'categorySummaries' => $categorySummaries,
            'movements' => $movements,
            'accountNames' => $this->allowedAccountNames(),
            'hairLengths' => $this->hairLengths,
            'categoryOptions' => $visibleCategoryOptions,
        ]);
    }

    public function updatedProductFormCategory($category): void
    {
        $this->productForm['unit'] = $this->unitForCategory((string) $category);

        if (! $this->categoryHasHairAttributes((string) $category)) {
            $this->productForm['color'] = '';
            $this->productForm['length_cm'] = null;
        }
    }

    public function showNewProductModal(): void
    {
        abort_unless($this->canAddInventoryProduct(), 403);

        $this->editingProductId = null;
        $this->resetProductForm();

        if ($this->account === 'perfumes') {
            $this->productForm['category'] = 'perfumes';
            $this->productForm['unit'] = $this->unitForCategory('perfumes');
        }
    }

    public function showEditProductModal(int $productId): void
    {
        abort_unless($this->canManageInventory(), 403);

        $product = $this->productQuery()->findOrFail($productId);

        $this->editingProductId = $product->id;
        $this->productForm = [
            'category' => $product->category,
            'name' => $product->name,
            'sku' => $product->sku,
            'color' => $product->color,
            'length_cm' => $product->length_cm,
            'unit' => $product->unit,
            'stock_quantity' => $this->formatNumericInput($product->stock_quantity, $product->unit),
            'unit_price' => $product->unit_price,
            'low_stock_threshold' => $this->formatNumericInput($product->low_stock_threshold, $product->unit),
            'is_active' => (bool) $product->is_active,
            'note' => $product->note,
        ];
        $this->editingProductImagePath = $product->image_path;
        $this->reset('productImage');
    }

    public function saveProduct(): void
    {
        abort_unless($this->editingProductId ? $this->canManageInventory() : $this->canAddInventoryProduct(), 403);

        if ($this->account === 'perfumes') {
            $this->productForm['category'] = 'perfumes';
            $this->productForm['unit'] = $this->unitForCategory('perfumes');
        }

        $this->validateProductForm();

        $newImagePath = $this->productImage?->store('inventory-products', 'public');
        $oldImagePath = null;

        try {
            DB::transaction(function () use ($newImagePath, &$oldImagePath) {
                $openingStock = (float) $this->productForm['stock_quantity'];

                $payload = [
                    'account' => $this->account,
                    'category' => $this->blankToNull($this->productForm['category']),
                    'name' => trim($this->productForm['name']),
                    'sku' => $this->blankToNull($this->productForm['sku']),
                    'color' => $this->categoryHasHairAttributes($this->productForm['category']) ? $this->blankToNull($this->productForm['color']) : null,
                    'length_cm' => $this->categoryHasHairAttributes($this->productForm['category']) ? $this->blankToNull($this->productForm['length_cm']) : null,
                    'unit' => $this->unitForCategory($this->productForm['category']),
                    'unit_price' => $this->blankToNull($this->productForm['unit_price']),
                    'low_stock_threshold' => $this->blankToNull($this->productForm['low_stock_threshold']),
                    'is_active' => (bool) $this->productForm['is_active'],
                    'note' => $this->blankToNull($this->productForm['note']),
                ];

                if ($newImagePath) {
                    $payload['image_path'] = $newImagePath;
                }

                if ($this->editingProductId) {
                    $product = $this->productQuery()->lockForUpdate()->findOrFail($this->editingProductId);
                    $oldStock = (float) $product->stock_quantity;
                    $oldImagePath = $product->image_path;

                    if ($this->canManageInventory()) {
                        $payload['stock_quantity'] = $openingStock;
                    }

                    $product->update($payload);

                    if ($this->canManageInventory() && $openingStock !== $oldStock) {
                        $this->createMovement($product, 'adjustment', $openingStock - $oldStock, $product->unit_price, 'Admin stock correction');
                    }
                } else {
                    $product = InventoryProduct::create($payload + [
                        'stock_quantity' => $openingStock,
                    ]);
                }

                if (! $this->editingProductId && $openingStock > 0) {
                    $this->createMovement($product, 'purchase', $openingStock, $product->unit_price, 'Opening stock');
                }
            });
        } catch (\Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $this->resetProductForm();
        $this->editingProductId = null;
        $this->dispatch('closeModal', elementId: '#inventoryProductModal');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function showStockModal(int $productId): void
    {
        abort_unless($this->canAddInventoryStock(), 403);

        $product = $this->productQuery()->findOrFail($productId);

        $this->stockProductId = $product->id;
        $this->stockForm = [
            'type' => 'purchase',
            'quantity' => null,
            'unit_price' => $product->unit_price,
            'note' => '',
        ];
    }

    public function saveStockMovement(): void
    {
        abort_unless($this->canAddInventoryStock(), 403);

        if (! $this->canManageInventory()) {
            $this->stockForm['type'] = 'purchase';
        }

        $this->validate([
            'stockForm.type' => ['required', Rule::in($this->canManageInventory() ? ['purchase', 'decrease', 'adjustment'] : ['purchase'])],
            'stockForm.quantity' => ['required', 'numeric', 'min:0'],
            'stockForm.unit_price' => ['nullable', 'numeric', 'min:0'],
            'stockForm.note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () {
            $product = $this->productQuery()->lockForUpdate()->findOrFail($this->stockProductId);
            $quantity = (float) $this->stockForm['quantity'];

            if ($this->stockForm['type'] === 'purchase') {
                if ($quantity <= 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stockForm.quantity' => 'Quantity must be greater than zero.',
                    ]);
                }

                $product->stock_quantity = (float) $product->stock_quantity + $quantity;
                $product->save();
                $this->createMovement($product, 'purchase', $quantity, $this->stockForm['unit_price'], $this->stockForm['note']);

                return;
            }

            if ($this->stockForm['type'] === 'decrease') {
                if ($quantity <= 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stockForm.quantity' => 'Quantity must be greater than zero.',
                    ]);
                }

                $product->stock_quantity = (float) $product->stock_quantity - $quantity;
                $product->save();
                $this->createMovement($product, 'adjustment', -1 * $quantity, $this->stockForm['unit_price'], $this->stockForm['note'] ?: 'Admin stock decrease');

                return;
            }

            $oldStock = (float) $product->stock_quantity;
            $product->stock_quantity = $quantity;
            $product->save();
            $this->createMovement($product, 'adjustment', $quantity - $oldStock, $this->stockForm['unit_price'], $this->stockForm['note']);
        });

        $this->stockProductId = null;
        $this->dispatch('closeModal', elementId: '#inventoryStockModal');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function deleteProduct(int $productId): void
    {
        abort_unless($this->canManageInventory(), 403);
        abort_unless($this->confirmedProductId === $productId, 403);

        $this->productQuery()->findOrFail($productId)->delete();

        $this->confirmedProductId = null;
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function confirmDeleteProduct(int $productId): void
    {
        abort_unless($this->canManageInventory(), 403);

        $this->confirmedProductId = $productId;
    }

    public function toggleProductStatus(int $productId): void
    {
        abort_unless($this->canManageInventory(), 403);

        $product = $this->productQuery()->findOrFail($productId);
        $product->update(['is_active' => ! $product->is_active]);
    }

    public function canManageInventory(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->hasRole('Admin') || $user?->can('manage assets inventory'));
    }

    public function canAddInventoryStock(): bool
    {
        $user = Auth::user();

        return (bool) ($this->canManageInventory()
            || ($user?->canAccessAccountBranch($this->account)
                && ($user?->can('view accounts '.$this->account) || $user?->can('view assets inventory'))));
    }

    public function canAddInventoryProduct(): bool
    {
        return $this->canAddInventoryStock();
    }

    public function formatQuantity($quantity, ?string $unit = null): string
    {
        $decimals = $unit === 'gram' ? 3 : 0;
        $formatted = number_format((float) $quantity, $decimals);

        return $unit === 'gram'
            ? rtrim(rtrim($formatted, '0'), '.')
            : $formatted;
    }

    public function categoryLabel(?string $category): string
    {
        if (! $category) {
            return '---';
        }

        if (isset($this->categoryOptions[$category])) {
            return __($this->categoryOptions[$category]['label']);
        }

        return $category;
    }

    public function categoryHasHairAttributes(?string $category = null): bool
    {
        $category = $category ?: ($this->productForm['category'] ?? null);

        return (bool) ($this->categoryOptions[$category]['has_hair_attributes'] ?? false);
    }

    public function unitShortLabel(?string $unit): string
    {
        return $unit === 'gram' ? __('ui.gram') : __('ui.piece');
    }

    public function productImageUrl(InventoryProduct $product): ?string
    {
        return $product->image_path
            ? Storage::disk('public')->url($product->image_path)
            : null;
    }

    public function editingProductImageUrl(): ?string
    {
        return $this->editingProductImagePath
            ? Storage::disk('public')->url($this->editingProductImagePath)
            : null;
    }

    private function validateProductForm(): void
    {
        $quantityRules = $this->unitForCategory($this->productForm['category']) === 'gram'
            ? ['numeric']
            : ['integer'];
        $stockRules = array_merge([$this->editingProductId ? 'nullable' : 'required'], $quantityRules);
        $lowStockRules = array_merge(['nullable'], $quantityRules, ['min:0']);

        if (! $this->canManageInventory()) {
            $stockRules[] = 'min:0';
        }

        $this->validate([
            'account' => ['required', Rule::in(array_keys($this->allowedAccountNames()))],
            'productForm.category' => ['required', Rule::in(array_keys($this->visibleCategoryOptions()))],
            'productForm.name' => ['required', 'string', 'max:255'],
            'productForm.sku' => ['nullable', 'string', 'max:255'],
            'productForm.color' => ['nullable', 'string', 'max:255'],
            'productForm.length_cm' => ['nullable', Rule::in($this->hairLengths)],
            'productForm.unit' => ['required', Rule::in(['piece', 'gram'])],
            'productForm.stock_quantity' => $stockRules,
            'productForm.unit_price' => ['nullable', 'numeric', 'min:0'],
            'productForm.low_stock_threshold' => $lowStockRules,
            'productForm.is_active' => ['boolean'],
            'productForm.note' => ['nullable', 'string', 'max:2000'],
            'productImage' => ['nullable', 'image', 'max:5120'],
        ]);
    }

    private function resetProductForm(): void
    {
        $this->reset('productImage');
        $this->editingProductImagePath = null;
        $this->productForm = [
            'category' => '',
            'name' => '',
            'sku' => '',
            'color' => '',
            'length_cm' => null,
            'unit' => 'piece',
            'stock_quantity' => 0,
            'unit_price' => null,
            'low_stock_threshold' => null,
            'is_active' => true,
            'note' => '',
        ];
    }

    private function formatNumericInput(mixed $value, ?string $unit = null): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($unit !== 'gram') {
            return (string) (int) ((float) $value);
        }

        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    }

    private function createMovement(InventoryProduct $product, string $type, float $quantity, $unitPrice = null, ?string $note = null): InventoryMovement
    {
        $unitPrice = $this->blankToNull($unitPrice);

        return InventoryMovement::create([
            'inventory_product_id' => $product->id,
            'account' => $product->account,
            'type' => $type,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $unitPrice !== null ? round(abs($quantity) * (float) $unitPrice, 2) : null,
            'balance_after' => $product->fresh()->stock_quantity,
            'occurred_at' => now(),
            'note' => $this->blankToNull($note),
        ]);
    }

    private function productQuery()
    {
        return InventoryProduct::query()
            ->where('account', $this->account)
            ->whereIn('category', array_keys($this->visibleCategoryOptions()));
    }

    private function allowedAccountNames(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return collect($this->accountNames)
            ->filter(fn ($label, $account) => $user->canAccessAccountBranch($account)
                && ($user->can('view accounts '.$account) || $user->can('view assets inventory')))
            ->all();
    }

    private function blankToNull($value)
    {
        return filled($value) ? $value : null;
    }

    private function unitForCategory(?string $category): string
    {
        return $this->categoryOptions[$category]['unit'] ?? 'piece';
    }

    private function visibleCategoryOptions(): array
    {
        $categoryKeys = $this->accountCategoryKeys[$this->account] ?? array_keys($this->categoryOptions);

        return collect($this->categoryOptions)
            ->only($categoryKeys)
            ->all();
    }
}
