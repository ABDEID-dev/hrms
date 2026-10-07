<?php

namespace App\Livewire\Salon;

use App\Models\SalonService;
use Livewire\Component;
use Livewire\WithPagination;

class Services extends Component
{
    use WithPagination;

    public const BRANCHES = [
        'all' => 'الفرعين معًا',
        'avani' => 'أفاني',
        'night_cassia' => 'نايت كاسل',
    ];

    public $searchTerm = '';

    public $branchFilter = '';

    public $service;

    public $serviceName = '';

    public $serviceEnglishName = '';

    public $serviceBranch = 'all';

    public $servicePrice = '';

    public $servicePriceNote = '';

    public $serviceActive = true;

    public $isEdit = false;

    public $confirmedId;

    public function updatingSearchTerm(): void
    {
        $this->resetPage();
    }

    public function updatingBranchFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $services = SalonService::query()
            ->when($this->branchFilter !== '', fn ($query) => $query->where('branch', $this->branchFilter))
            ->when($this->searchTerm !== '', function ($query) {
                $query->where(function ($nested) {
                    $nested->where('name', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('branch', 'like', '%'.$this->searchTerm.'%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.salon.services', [
            'services' => $services,
            'branches' => self::BRANCHES,
        ]);
    }

    public function showNewServiceModal(): void
    {
        $this->resetForm();
        $this->serviceBranch = 'all';
        $this->serviceActive = true;
    }

    public function showEditServiceModal(int $serviceId): void
    {
        $service = SalonService::findOrFail($serviceId);

        $this->resetForm();
        $this->isEdit = true;
        $this->service = $service;
        $this->serviceName = $service->name;
        $this->serviceEnglishName = $service->english_name;
        $this->serviceBranch = $service->branch;
        $this->servicePrice = $service->price;
        $this->servicePriceNote = $service->price_note;
        $this->serviceActive = $service->is_active;
    }

    public function submitService(): void
    {
        $this->validate([
            'serviceName' => 'required|string|min:2|max:255',
            'serviceEnglishName' => 'nullable|string|min:2|max:255',
            'serviceBranch' => 'required|in:all,avani,night_cassia',
            'servicePrice' => 'required|numeric|min:0',
            'servicePriceNote' => 'nullable|string|max:255',
            'serviceActive' => 'required|boolean',
        ]);

        $payload = [
            'name' => trim($this->serviceName),
            'english_name' => filled($this->serviceEnglishName) ? trim($this->serviceEnglishName) : null,
            'branch' => $this->serviceBranch,
            'price' => $this->servicePrice,
            'price_note' => filled($this->servicePriceNote) ? trim($this->servicePriceNote) : null,
            'is_active' => $this->serviceActive,
        ];

        if ($this->isEdit && $this->service instanceof SalonService) {
            $this->service->update($payload);
            $message = __('Service updated successfully.');
        } else {
            SalonService::create($payload);
            $message = __('Service added successfully.');
        }

        $this->dispatch('closeModal', elementId: '#salonServiceModal');
        $this->dispatch('toastr', type: 'success', message: $message);
        $this->resetForm();
    }

    public function confirmDeleteService(int $serviceId): void
    {
        $this->confirmedId = $serviceId;
    }

    public function deleteService(int $serviceId): void
    {
        SalonService::findOrFail($serviceId)->delete();
        $this->dispatch('toastr', type: 'success', message: __('Service deleted successfully.'));
    }

    private function resetForm(): void
    {
        $this->reset('service', 'serviceName', 'serviceEnglishName', 'servicePrice', 'servicePriceNote', 'confirmedId', 'isEdit');
        $this->serviceBranch = 'all';
        $this->serviceActive = true;
    }
}
