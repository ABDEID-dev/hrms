<?php

namespace App\Livewire\Assets;

use App\Models\MaktoomDye;
use App\Models\MaktoomDyeMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class MaktoomDyes extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingDyeId = null;

    public ?int $movementDyeId = null;

    public array $dyeForm = [
        'code' => '',
        'name' => '',
        'warehouse_stock' => 0,
    ];

    public array $movementForm = [
        'quantity' => null,
        'note' => '',
    ];

    public function mount(): void
    {
        abort_unless($this->canView(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $dyes = MaktoomDye::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('note', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByRaw('CAST(code AS UNSIGNED)')
            ->orderBy('code')
            ->paginate(15);

        $summary = MaktoomDye::query()
            ->selectRaw('count(*) as dyes_count')
            ->selectRaw('coalesce(sum(warehouse_stock), 0) as warehouse_stock')
            ->first();

        $movements = MaktoomDyeMovement::query()
            ->with('dye')
            ->whereIn('type', ['purchase', 'warehouse_adjustment'])
            ->latest('occurred_at')
            ->latest('id')
            ->take(25)
            ->get();

        return view('livewire.assets.maktoom-dyes', compact('dyes', 'summary', 'movements'));
    }

    public function showNewDyeModal(): void
    {
        abort_unless($this->canOperate(), 403);
        $this->editingDyeId = null;
        $this->resetDyeForm();
    }

    public function showEditDyeModal(int $dyeId): void
    {
        abort_unless($this->canManage(), 403);
        $dye = MaktoomDye::findOrFail($dyeId);

        $this->editingDyeId = $dye->id;
        $this->dyeForm = [
            'code' => $dye->code,
            'name' => $dye->name,
            'warehouse_stock' => $dye->warehouse_stock,
        ];
    }

    public function saveDye(): void
    {
        abort_unless($this->editingDyeId ? $this->canManage() : $this->canOperate(), 403);

        $validated = $this->validate([
            'dyeForm.code' => ['required', 'string', 'max:50', Rule::unique('maktoom_dyes', 'code')->ignore($this->editingDyeId)->whereNull('deleted_at')],
            'dyeForm.name' => ['required', 'string', 'max:255'],
            'dyeForm.warehouse_stock' => [$this->editingDyeId ? 'nullable' : 'required', 'integer', 'min:0'],
        ])['dyeForm'];

        DB::transaction(function () use ($validated) {
            $warehouseStock = (int) ($validated['warehouse_stock'] ?? 0);
            $payload = [
                'code' => trim($validated['code']),
                'name' => trim($validated['name']),
            ];

            if ($this->editingDyeId) {
                MaktoomDye::findOrFail($this->editingDyeId)->update($payload);

                return;
            }

            $dye = MaktoomDye::create($payload + [
                'warehouse_stock' => $warehouseStock,
                'shop_stock' => 0,
                'sold_quantity' => 0,
            ]);

            if ($warehouseStock > 0) {
                $this->createMovement($dye, 'purchase', $warehouseStock, __('ui.opening_dye_stock'));
            }
        });

        $this->editingDyeId = null;
        $this->resetDyeForm();
        $this->dispatch('closeModal', elementId: '#maktoomDyeModal');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function showMovementModal(int $dyeId): void
    {
        abort_unless($this->canOperate(), 403);
        $dye = MaktoomDye::findOrFail($dyeId);
        abort_unless($dye->is_active, 403);

        $this->movementDyeId = $dye->id;
        $this->movementForm = [
            'quantity' => null,
            'note' => '',
        ];
    }

    public function saveMovement(): void
    {
        abort_unless($this->canOperate(), 403);

        $validated = $this->validate([
            'movementForm.quantity' => ['required', 'integer', 'min:1'],
            'movementForm.note' => ['nullable', 'string', 'max:2000'],
        ])['movementForm'];

        DB::transaction(function () use ($validated) {
            $dye = MaktoomDye::query()->lockForUpdate()->findOrFail($this->movementDyeId);
            $quantity = (int) $validated['quantity'];

            $dye->warehouse_stock += $quantity;
            $dye->save();
            $this->createMovement($dye, 'purchase', $quantity, $validated['note'] ?? null);
        });

        $this->movementDyeId = null;
        $this->dispatch('closeModal', elementId: '#maktoomDyeMovementModal');
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

        return $this->canManage()
            || (bool) ($user?->canAccessAccountBranch('maktoom') && $user?->can('operate maktoom dyes'));
    }

    public function canManage(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->hasRole('Admin')
            || ($user?->canAccessAccountBranch('maktoom') && $user?->can('manage maktoom dyes')));
    }

    private function createMovement(MaktoomDye $dye, string $type, int $quantity, ?string $note): void
    {
        MaktoomDyeMovement::create([
            'maktoom_dye_id' => $dye->id,
            'type' => $type,
            'quantity' => $quantity,
            'warehouse_balance_after' => $dye->warehouse_stock,
            'shop_balance_after' => (int) ($dye->shop_stock ?? 0),
            'sold_quantity_after' => (int) ($dye->sold_quantity ?? 0),
            'occurred_at' => now(),
            'note' => filled($note) ? trim($note) : null,
        ]);
    }

    private function resetDyeForm(): void
    {
        $this->dyeForm = [
            'code' => '',
            'name' => '',
            'warehouse_stock' => 0,
        ];
    }
}
