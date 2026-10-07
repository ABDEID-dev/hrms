<?php

namespace App\Livewire\Assets;

use App\Models\Employee;
use App\Models\MaktoomDye;
use App\Models\MaktoomDyeRevenueItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MaktoomDyeReports extends Component
{
    use WithPagination;

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $employeeId = '';

    public string $dyeId = '';

    public function mount(): void
    {
        abort_unless($this->canView(), 403);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'dateFrom', 'dateTo', 'employeeId', 'dyeId'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = MaktoomDyeRevenueItem::query()
            ->with(['revenue.employee', 'dye'])
            ->whereHas('revenue')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('dye', function ($query) {
                        $query->where('code', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%');
                    })->orWhereHas('revenue.employee', function ($query) {
                        $query->where('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('father_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%');
                    })->orWhereHas('revenue', fn ($query) => $query->where('note', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->dateFrom !== '', fn ($query) => $query->whereHas(
                'revenue',
                fn ($query) => $query->whereDate('date', '>=', $this->dateFrom)
            ))
            ->when($this->dateTo !== '', fn ($query) => $query->whereHas(
                'revenue',
                fn ($query) => $query->whereDate('date', '<=', $this->dateTo)
            ))
            ->when($this->employeeId !== '', fn ($query) => $query->whereHas(
                'revenue',
                fn ($query) => $query->where('employee_id', $this->employeeId)
            ))
            ->when($this->dyeId !== '', fn ($query) => $query->where('maktoom_dye_id', $this->dyeId));

        $summary = (clone $query)
            ->selectRaw('count(*) as deductions_count')
            ->selectRaw('coalesce(sum(quantity), 0) as deducted_quantity')
            ->first();

        $items = $query
            ->orderByDesc(
                \App\Models\MaktoomDyeRevenue::query()
                    ->select('date')
                    ->whereColumn('maktoom_dye_revenues.id', 'maktoom_dye_revenue_items.maktoom_dye_revenue_id')
                    ->limit(1)
            )
            ->latest('id')
            ->paginate(20);

        $employees = Employee::query()
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $dyes = MaktoomDye::query()
            ->orderByRaw('CAST(code AS UNSIGNED)')
            ->orderBy('code')
            ->get();

        $currentStock = MaktoomDye::query()->sum('warehouse_stock');

        return view('livewire.assets.maktoom-dye-reports', compact(
            'items',
            'summary',
            'employees',
            'dyes',
            'currentStock'
        ));
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'dateFrom', 'dateTo', 'employeeId', 'dyeId']);
        $this->resetPage();
    }

    public function canView(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->hasRole('Admin')
            || ($user?->canAccessAccountBranch('maktoom') && $user?->can('view maktoom dyes')));
    }
}
