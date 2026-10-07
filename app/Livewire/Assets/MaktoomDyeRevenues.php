<?php

namespace App\Livewire\Assets;

use App\Models\Employee;
use App\Models\MaktoomDye;
use App\Models\MaktoomDyeMovement;
use App\Models\MaktoomDyeRevenue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class MaktoomDyeRevenues extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingRevenueId = null;

    public ?int $confirmedRevenueId = null;

    public array $form = [
        'employee_id' => null,
        'date' => null,
        'note' => '',
        'items' => [
            ['dye_id' => null, 'quantity' => 1],
        ],
    ];

    public function mount(): void
    {
        abort_unless($this->canView(), 403);
        $this->form['date'] = now('Asia/Dubai')->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $employees = Employee::query()
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $dyes = MaktoomDye::query()
            ->where('is_active', true)
            ->orderByRaw('CAST(code AS UNSIGNED)')
            ->orderBy('code')
            ->get();

        $revenues = MaktoomDyeRevenue::query()
            ->with(['employee', 'items.dye'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('employee', function ($query) {
                        $query->where('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('father_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%');
                    })->orWhereHas('items.dye', function ($query) {
                        $query->where('code', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%');
                    })->orWhere('note', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('date')
            ->latest('id')
            ->paginate(15);

        return view('livewire.assets.maktoom-dye-revenues', compact('employees', 'dyes', 'revenues'));
    }

    public function addItem(): void
    {
        abort_unless($this->canOperate(), 403);
        $this->form['items'][] = ['dye_id' => null, 'quantity' => 1];
    }

    public function removeItem(int $index): void
    {
        abort_unless($this->canOperate(), 403);

        if (count($this->form['items']) === 1) {
            return;
        }

        unset($this->form['items'][$index]);
        $this->form['items'] = array_values($this->form['items']);
    }

    public function save(): void
    {
        abort_unless($this->canOperate(), 403);

        $validated = $this->validate([
            'form.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'form.date' => ['required', 'date', 'before_or_equal:today'],
            'form.note' => ['nullable', 'string', 'max:2000'],
            'form.items' => ['required', 'array', 'min:1'],
            'form.items.*.dye_id' => ['required', 'integer', 'distinct', 'exists:maktoom_dyes,id'],
            'form.items.*.quantity' => ['required', 'integer', 'min:1'],
        ])['form'];

        DB::transaction(fn () => $this->persistRevenue($validated));

        $this->resetForm();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function editRevenue(int $revenueId): void
    {
        abort_unless($this->canAdminister(), 403);

        $revenue = MaktoomDyeRevenue::query()
            ->with('items')
            ->findOrFail($revenueId);

        $this->editingRevenueId = $revenue->id;
        $this->confirmedRevenueId = null;
        $this->form = [
            'employee_id' => $revenue->employee_id,
            'date' => $revenue->date?->toDateString(),
            'note' => $revenue->note ?? '',
            'items' => $revenue->items
                ->map(fn ($item) => [
                    'dye_id' => $item->maktoom_dye_id,
                    'quantity' => $item->quantity,
                ])
                ->values()
                ->all(),
        ];

        $this->dispatch('scrollToDyeRevenueForm');
    }

    public function cancelEdit(): void
    {
        abort_unless($this->canAdminister(), 403);
        $this->resetForm();
    }

    public function confirmDeleteRevenue(int $revenueId): void
    {
        abort_unless($this->canAdminister(), 403);
        MaktoomDyeRevenue::findOrFail($revenueId);
        $this->confirmedRevenueId = $revenueId;
    }

    public function deleteRevenue(int $revenueId): void
    {
        abort_unless($this->canAdminister(), 403);
        abort_unless($this->confirmedRevenueId === $revenueId, 403);

        DB::transaction(function () use ($revenueId) {
            $revenue = MaktoomDyeRevenue::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($revenueId);
            $dyes = MaktoomDye::query()
                ->whereIn('id', $revenue->items->pluck('maktoom_dye_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $this->restoreRevenueStock($revenue, $dyes, __('ui.dye_revenue_deleted_note', ['id' => $revenue->id]));
            $revenue->delete();
        });

        if ($this->editingRevenueId === $revenueId) {
            $this->resetForm();
        }

        $this->confirmedRevenueId = null;
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function canView(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->hasRole('Admin')
            || ($user?->canAccessAccountBranch('maktoom') && $user?->can('view maktoom dyes')));
    }

    public function canOperate(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->hasRole('Admin')
            || ($user?->canAccessAccountBranch('maktoom') && $user?->can('operate maktoom dyes')));
    }

    public function canAdminister(): bool
    {
        return (bool) Auth::user()?->hasRole('Admin');
    }

    private function persistRevenue(array $validated): void
    {
        $items = collect($validated['items'])->sortBy('dye_id')->values();
        $revenue = $this->editingRevenueId
            ? MaktoomDyeRevenue::query()->with('items')->lockForUpdate()->findOrFail($this->editingRevenueId)
            : null;
        $dyeIds = $items->pluck('dye_id')
            ->merge($revenue?->items->pluck('maktoom_dye_id') ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();
        $dyes = MaktoomDye::query()
            ->whereIn('id', $dyeIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($revenue) {
            abort_unless($this->canAdminister(), 403);
            $this->restoreRevenueStock($revenue, $dyes, __('ui.dye_revenue_edited_restore_note', ['id' => $revenue->id]));
        }

        $this->validateAvailableDyes($items, $dyes);

        $payload = [
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'total_quantity' => $items->sum('quantity'),
            'note' => filled($validated['note']) ? trim($validated['note']) : null,
        ];

        if ($revenue) {
            $revenue->update($payload);
        } else {
            $revenue = MaktoomDyeRevenue::create($payload);
        }

        $existingItems = $revenue->items->keyBy('maktoom_dye_id');
        $keptDyeIds = [];

        foreach ($items as $item) {
            $dye = $dyes->get((int) $item['dye_id']);
            $quantity = (int) $item['quantity'];
            $stockBefore = $dye->warehouse_stock;
            $keptDyeIds[] = $dye->id;

            $dye->warehouse_stock -= $quantity;
            $dye->sold_quantity += $quantity;
            $dye->save();

            $itemPayload = [
                'maktoom_dye_id' => $dye->id,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $dye->warehouse_stock,
            ];
            $existingItem = $existingItems->get($dye->id);

            $existingItem
                ? $existingItem->update($itemPayload)
                : $revenue->items()->create($itemPayload);

            $this->createStockMovement(
                $dye,
                'sale_from_warehouse',
                $quantity,
                __('ui.dye_revenue_movement_note', [
                    'id' => $revenue->id,
                    'employee' => $revenue->employee->full_name,
                ])
            );
        }

        $revenue->items()
            ->whereNotIn('maktoom_dye_id', $keptDyeIds)
            ->get()
            ->each
            ->forceDelete();
    }

    private function validateAvailableDyes($items, $dyes): void
    {
        foreach ($items as $index => $item) {
            $dye = $dyes->get((int) $item['dye_id']);
            $quantity = (int) $item['quantity'];

            if (! $dye || ! $dye->is_active) {
                throw ValidationException::withMessages([
                    "form.items.$index.dye_id" => __('ui.dye_not_available'),
                ]);
            }

            if ($quantity > $dye->warehouse_stock) {
                throw ValidationException::withMessages([
                    "form.items.$index.quantity" => __('ui.insufficient_dye_stock', [
                        'available' => $dye->warehouse_stock,
                    ]),
                ]);
            }
        }
    }

    private function restoreRevenueStock(MaktoomDyeRevenue $revenue, $dyes, string $note): void
    {
        foreach ($revenue->items as $item) {
            $dye = $dyes->get($item->maktoom_dye_id);

            if (! $dye) {
                continue;
            }

            $dye->warehouse_stock += $item->quantity;
            $dye->sold_quantity = max(0, $dye->sold_quantity - $item->quantity);
            $dye->save();

            $this->createStockMovement($dye, 'purchase', $item->quantity, $note);
        }
    }

    private function createStockMovement(MaktoomDye $dye, string $type, int $quantity, string $note): void
    {
        MaktoomDyeMovement::create([
            'maktoom_dye_id' => $dye->id,
            'type' => $type,
            'quantity' => $quantity,
            'warehouse_balance_after' => $dye->warehouse_stock,
            'shop_balance_after' => (int) $dye->shop_stock,
            'sold_quantity_after' => $dye->sold_quantity,
            'occurred_at' => now(),
            'note' => $note,
        ]);
    }

    private function resetForm(): void
    {
        $this->editingRevenueId = null;
        $this->confirmedRevenueId = null;
        $this->form = [
            'employee_id' => null,
            'date' => now('Asia/Dubai')->toDateString(),
            'note' => '',
            'items' => [
                ['dye_id' => null, 'quantity' => 1],
            ],
        ];
    }
}
