<?php

namespace App\Livewire\Assets;

use App\Models\InventoryMovement;
use App\Models\InventoryProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Reports extends Component
{
    use WithPagination;

    public string $account = 'all';

    public string $category = 'all';

    public string $search = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    private array $accountNames = [
        'maktoom' => 'Maktoum',
        'avani' => 'Avani',
        'perfumes' => 'Perfumes',
    ];

    private array $categoryOptions = [
        'double_face_hair' => [
            'label' => 'ui.inventory_category_double_face_hair',
            'unit' => 'piece',
        ],
        'iranian_hair' => [
            'label' => 'ui.inventory_category_iranian_hair',
            'unit' => 'gram',
        ],
        'indian_hair' => [
            'label' => 'ui.inventory_category_indian_hair',
            'unit' => 'gram',
        ],
        'hair_care_products' => [
            'label' => 'ui.inventory_category_hair_care_products',
            'unit' => 'piece',
        ],
        'perfumes' => [
            'label' => 'ui.inventory_category_perfumes',
            'unit' => 'piece',
        ],
        'other' => [
            'label' => 'ui.inventory_category_other',
            'unit' => 'piece',
        ],
    ];

    public function mount(): void
    {
        abort_if($this->allowedAccountNames() === [], 403);

        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updating($property): void
    {
        if (in_array($property, ['account', 'category', 'search', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage('productsPage');
            $this->resetPage('movementsPage');
        }
    }

    public function render()
    {
        $this->validateFilters();

        $productsQuery = $this->productsQuery();
        $movementsQuery = $this->movementsQuery();

        $stockSummary = (clone $productsQuery)
            ->selectRaw('count(*) as products_count')
            ->selectRaw("sum(case when unit = 'piece' then stock_quantity else 0 end) as pieces_stock")
            ->selectRaw("sum(case when unit = 'piece' then sold_quantity else 0 end) as pieces_sold")
            ->selectRaw("sum(case when unit = 'gram' then stock_quantity else 0 end) as grams_stock")
            ->selectRaw("sum(case when unit = 'gram' then sold_quantity else 0 end) as grams_sold")
            ->selectRaw('sum(coalesce(stock_quantity, 0) * coalesce(unit_price, 0)) as stock_value')
            ->first();

        $movementSummary = (clone $movementsQuery)
            ->selectRaw("sum(case when type = 'purchase' then quantity else 0 end) as purchased_quantity")
            ->selectRaw("sum(case when type = 'sale' then abs(quantity) else 0 end) as sold_quantity")
            ->selectRaw("sum(case when type = 'adjustment' then quantity else 0 end) as adjusted_quantity")
            ->selectRaw("sum(case when type = 'sale' then coalesce(total_amount, 0) else 0 end) as sales_amount")
            ->selectRaw("sum(case when type = 'purchase' then coalesce(total_amount, 0) else 0 end) as purchase_amount")
            ->first();

        $accountSummaries = (clone $productsQuery)
            ->select('account')
            ->selectRaw('count(*) as products_count')
            ->selectRaw("sum(case when unit = 'piece' then stock_quantity else 0 end) as pieces_stock")
            ->selectRaw("sum(case when unit = 'piece' then sold_quantity else 0 end) as pieces_sold")
            ->selectRaw("sum(case when unit = 'gram' then stock_quantity else 0 end) as grams_stock")
            ->selectRaw("sum(case when unit = 'gram' then sold_quantity else 0 end) as grams_sold")
            ->selectRaw('sum(coalesce(stock_quantity, 0) * coalesce(unit_price, 0)) as stock_value')
            ->groupBy('account')
            ->orderBy('account')
            ->get();

        $categorySummaries = (clone $productsQuery)
            ->select('category', 'unit')
            ->selectRaw('count(*) as products_count')
            ->selectRaw('sum(stock_quantity) as stock_quantity')
            ->selectRaw('sum(sold_quantity) as sold_quantity')
            ->selectRaw('sum(coalesce(stock_quantity, 0) * coalesce(unit_price, 0)) as stock_value')
            ->groupBy('category', 'unit')
            ->orderBy('category')
            ->get();

        $lowStockProducts = (clone $productsQuery)
            ->whereNotNull('low_stock_threshold')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity')
            ->take(12)
            ->get();

        $products = (clone $productsQuery)
            ->orderBy('account')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(25, ['*'], 'productsPage');

        $movements = $movementsQuery
            ->with('product')
            ->latest('occurred_at')
            ->latest('id')
            ->paginate(25, ['*'], 'movementsPage');

        return view('livewire.assets.reports', [
            'accountNames' => $this->allowedAccountNames(),
            'categoryOptions' => $this->categoryOptions,
            'stockSummary' => $stockSummary,
            'movementSummary' => $movementSummary,
            'accountSummaries' => $accountSummaries,
            'categorySummaries' => $categorySummaries,
            'lowStockProducts' => $lowStockProducts,
            'products' => $products,
            'movements' => $movements,
        ]);
    }

    public function resetFilters(): void
    {
        $this->account = 'all';
        $this->category = 'all';
        $this->search = '';
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
        $this->resetPage('productsPage');
        $this->resetPage('movementsPage');
    }

    public function formatQuantity($quantity, ?string $unit = null): string
    {
        $decimals = $unit === 'gram' ? 3 : 0;
        $formatted = number_format((float) $quantity, $decimals);

        return $unit === 'gram'
            ? rtrim(rtrim($formatted, '0'), '.')
            : $formatted;
    }

    public function accountLabel(?string $account): string
    {
        return $account && isset($this->accountNames[$account])
            ? __('ui.inventory_account_'.$account)
            : '---';
    }

    public function categoryLabel(?string $category): string
    {
        return $category && isset($this->categoryOptions[$category])
            ? __($this->categoryOptions[$category]['label'])
            : ($category ?: '---');
    }

    public function movementTypeLabel(?string $type): string
    {
        return match ($type) {
            'purchase' => app()->getLocale() === 'ar' ? 'إضافة' : 'Purchase',
            'sale' => app()->getLocale() === 'ar' ? 'بيع' : 'Sale',
            'adjustment' => app()->getLocale() === 'ar' ? 'تسوية' : 'Adjustment',
            default => $type ?: '---',
        };
    }

    private function productsQuery(): Builder
    {
        return InventoryProduct::query()
            ->whereIn('account', $this->selectedAccounts())
            ->when($this->category !== 'all', fn ($query) => $query->where('category', $this->category))
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%')
                        ->orWhere('category', 'like', '%'.$this->search.'%')
                        ->orWhere('color', 'like', '%'.$this->search.'%');
                });
            });
    }

    private function movementsQuery(): Builder
    {
        return InventoryMovement::query()
            ->whereIn('account', $this->selectedAccounts())
            ->whereBetween('occurred_at', [$this->dateFromCarbon(), $this->dateToCarbon()])
            ->when($this->category !== 'all' || $this->search !== '', function ($query) {
                $query->whereHas('product', function ($query) {
                    $query
                        ->when($this->category !== 'all', fn ($query) => $query->where('category', $this->category))
                        ->when($this->search !== '', function ($query) {
                            $query->where(function ($query) {
                                $query->where('name', 'like', '%'.$this->search.'%')
                                    ->orWhere('sku', 'like', '%'.$this->search.'%')
                                    ->orWhere('category', 'like', '%'.$this->search.'%')
                                    ->orWhere('color', 'like', '%'.$this->search.'%');
                            });
                        });
                });
            });
    }

    private function selectedAccounts(): array
    {
        $allowedAccounts = array_keys($this->allowedAccountNames());

        return $this->account === 'all'
            ? $allowedAccounts
            : array_values(array_intersect([$this->account], $allowedAccounts));
    }

    private function allowedAccountNames(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return collect($this->accountNames)
            ->filter(fn ($label, $account) => $user->can('view accounts '.$account) && $user->canAccessAccountBranch($account))
            ->all();
    }

    private function validateFilters(): void
    {
        $this->validate([
            'account' => ['required', Rule::in(array_merge(['all'], array_keys($this->allowedAccountNames())))],
            'category' => ['required', Rule::in(array_merge(['all'], array_keys($this->categoryOptions)))],
            'search' => ['nullable', 'string', 'max:255'],
            'dateFrom' => ['required', 'date'],
            'dateTo' => ['required', 'date', 'after_or_equal:dateFrom'],
        ]);
    }

    private function dateFromCarbon(): Carbon
    {
        return Carbon::parse($this->dateFrom)->startOfDay();
    }

    private function dateToCarbon(): Carbon
    {
        return Carbon::parse($this->dateTo)->endOfDay();
    }
}
