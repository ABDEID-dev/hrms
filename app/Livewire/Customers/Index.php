<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\CustomerService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $searchTerm = '';

    public $customer;

    public $selectedCustomerId;

    public $customerName = '';

    public $customerEnglishName = '';

    public $customerPhone = '';

    public $customerNationality = '';

    public $customerNationalityEn = '';

    public $customerNote = '';

    public $serviceName = '';

    public $serviceNote = '';

    public $serviceCustomerId;

    public $isEdit = false;

    public $confirmedCustomerId;

    public function mount(): void
    {
        $this->selectedCustomerId = Customer::query()->latest('id')->value('id');
    }

    public function updatingSearchTerm(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $customers = Customer::query()
            ->withCount('services')
            ->when($this->searchTerm !== '', function ($query) {
                $query->where(function ($nested) {
                    $nested->where('name', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('english_name', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('phone', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('nationality', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('nationality_en', 'like', '%'.$this->searchTerm.'%');
                });
            })
            ->latest()
            ->paginate(10);

        $selectedCustomer = null;

        if ($this->selectedCustomerId) {
            $selectedCustomer = Customer::with(['services.employee'])->find($this->selectedCustomerId);
        }

        if (! $selectedCustomer) {
            $selectedCustomer = Customer::with(['services.employee'])->latest()->first();
            $this->selectedCustomerId = $selectedCustomer?->id;
        }

        return view('livewire.customers.index', [
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
        ]);
    }

    public function selectCustomer(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;
    }

    public function showNewCustomerModal(): void
    {
        $this->resetCustomerForm();
    }

    public function showEditCustomerModal(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);

        $this->resetCustomerForm();
        $this->isEdit = true;
        $this->customer = $customer;
        $this->customerName = $customer->name;
        $this->customerEnglishName = $customer->english_name;
        $this->customerPhone = $customer->phone;
        $this->customerNationality = $customer->nationality;
        $this->customerNationalityEn = $customer->nationality_en;
        $this->customerNote = $customer->note;
    }

    public function submitCustomer(): void
    {
        $this->validate([
            'customerName' => 'required|string|min:2|max:255',
            'customerEnglishName' => 'nullable|string|min:2|max:255',
            'customerPhone' => 'nullable|string|max:30',
            'customerNationality' => 'nullable|string|max:100',
            'customerNationalityEn' => 'nullable|string|max:100',
            'customerNote' => 'nullable|string|max:2000',
        ]);

        $payload = [
            'name' => trim($this->customerName),
            'english_name' => $this->customerEnglishName !== '' ? trim($this->customerEnglishName) : null,
            'phone' => $this->normalizePhone($this->customerPhone),
            'nationality' => $this->customerNationality !== '' ? trim($this->customerNationality) : null,
            'nationality_en' => $this->customerNationalityEn !== '' ? trim($this->customerNationalityEn) : null,
            'note' => $this->customerNote !== '' ? trim($this->customerNote) : null,
        ];

        if ($this->isEdit && $this->customer instanceof Customer) {
            $this->customer->update($payload);
            $message = __('Customer updated successfully.');
            $this->selectedCustomerId = $this->customer->id;
        } else {
            $customer = Customer::create($payload);
            $message = __('Customer added successfully.');
            $this->selectedCustomerId = $customer->id;
        }

        $this->dispatch('closeModal', elementId: '#customerModal');
        $this->dispatch('toastr', type: 'success', message: $message);
        $this->resetCustomerForm();
    }

    public function showNewServiceModal(int $customerId): void
    {
        $this->reset('serviceName', 'serviceNote');
        $this->serviceCustomerId = $customerId;
        $this->selectedCustomerId = $customerId;
    }

    public function submitService(): void
    {
        $this->validate([
            'serviceCustomerId' => 'required|exists:customers,id',
            'serviceName' => 'required|string|min:2|max:255',
            'serviceNote' => 'nullable|string|max:2000',
        ]);

        CustomerService::create([
            'customer_id' => $this->serviceCustomerId,
            'employee_id' => Auth::user()?->employee_id,
            'service_name' => trim($this->serviceName),
            'note' => $this->serviceNote !== '' ? trim($this->serviceNote) : null,
            'served_at' => now(),
        ]);

        $this->selectedCustomerId = $this->serviceCustomerId;
        $this->dispatch('closeModal', elementId: '#customerServiceModal');
        $this->dispatch('toastr', type: 'success', message: __('Service added to the customer record successfully.'));
        $this->reset('serviceName', 'serviceNote', 'serviceCustomerId');
    }

    public function confirmDeleteCustomer(int $customerId): void
    {
        $this->confirmedCustomerId = $customerId;
    }

    public function deleteCustomer(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);

        $customer->delete();

        if ($this->selectedCustomerId === $customer->id) {
            $this->selectedCustomerId = Customer::query()->latest('id')->value('id');
        }

        $this->confirmedCustomerId = null;
        $this->dispatch('toastr', type: 'success', message: __('Customer deleted successfully.'));
    }

    private function resetCustomerForm(): void
    {
        $this->reset(
            'customer',
            'isEdit',
            'customerName',
            'customerEnglishName',
            'customerPhone',
            'customerNationality',
            'customerNationalityEn',
            'customerNote'
        );
    }

    private function normalizePhone(?string $phone): ?string
    {
        $normalized = preg_replace('/[^\d+]+/', '', (string) $phone);

        return $normalized !== '' ? $normalized : null;
    }
}
