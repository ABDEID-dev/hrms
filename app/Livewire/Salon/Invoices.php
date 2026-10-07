<?php

namespace App\Livewire\Salon;

use App\Jobs\sendPendingMessagesByWhatsapp;
use App\Models\Customer;
use App\Models\CustomerService;
use App\Models\Employee;
use App\Models\Message;
use App\Models\SalonInvoice;
use App\Models\SalonInvoiceItem;
use App\Models\SalonService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Invoices extends Component
{
    public const BRAND_NAME_AR = 'هوركادا صالون';

    public const BRAND_NAME_EN = 'Hurghada Salon';

    public const BRANCHES = [
        'all' => 'الفرعين معًا',
        'avani' => 'أفاني',
        'night_cassia' => 'نايت كاسل',
    ];

    public const BRANCHES_EN = [
        'all' => 'All Branches',
        'avani' => 'Avani',
        'night_cassia' => 'Knight Castle Hotel',
    ];

    public const INVOICE_LANGUAGES = [
        'ar' => 'العربية',
        'en' => 'English',
    ];

    public $invoiceBranch = 'night_cassia';

    public $invoiceLanguage = 'en';

    public $customerId = '';

    public $paidAmount = '';

    public $paymentMethod = 'cash';

    public $note = '';

    public $invoiceEmployeeIds = [];

    public $latestInvoiceId;

    public $lineItems = [];

    public function mount(): void
    {
        $this->customerId = Customer::query()->latest('id')->value('id') ?? '';
        $this->lineItems = [$this->emptyLineItem()];
    }

    public function updatedInvoiceBranch(): void
    {
        $this->lineItems = [$this->emptyLineItem()];
        $this->paidAmount = '';
    }

    public function updatedLineItems($value, $key): void
    {
        if (! str_ends_with((string) $key, '.service_id')) {
            return;
        }

        $parts = explode('.', (string) $key);
        $index = (int) ($parts[0] ?? 0);
        $serviceId = (int) $value;

        if (! isset($this->lineItems[$index])) {
            return;
        }

        if ($serviceId <= 0) {
            $this->lineItems[$index]['price'] = '';
            $this->lineItems[$index]['service_name'] = '';
            $this->lineItems[$index]['service_name_en'] = '';

            return;
        }

        $service = SalonService::find($serviceId);

        if (! $service) {
            return;
        }

        if (in_array($service->branch, ['avani', 'night_cassia'], true)) {
            $this->invoiceBranch = $service->branch;
        }

        $this->lineItems[$index]['price'] = number_format((float) $service->price, 2, '.', '');
        $this->lineItems[$index]['service_name'] = $service->name;
        $this->lineItems[$index]['service_name_en'] = $service->english_name;
    }

    public function addServiceRow(): void
    {
        if (count($this->lineItems) >= 15) {
            $this->dispatch('toastr', type: 'warning', message: 'يمكن إضافة 15 خدمة كحد أقصى في الفاتورة الواحدة.');

            return;
        }

        $this->lineItems[] = $this->emptyLineItem();
    }

    public function removeServiceRow(int $index): void
    {
        if (! isset($this->lineItems[$index])) {
            return;
        }

        unset($this->lineItems[$index]);
        $this->lineItems = array_values($this->lineItems);

        if ($this->lineItems === []) {
            $this->lineItems = [$this->emptyLineItem()];
        }
    }

    public function render()
    {
        $supportsInvoiceItemEmployees = $this->supportsInvoiceItemEmployees();
        $supportsInvoiceEmployees = $this->supportsInvoiceEmployees();
        $customers = Customer::query()->latest()->get();
        $employees = Employee::query()
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $services = SalonService::query()
            ->where('is_active', true)
            ->whereIn('branch', [$this->invoiceBranch, 'all'])
            ->orderBy('name')
            ->get();

        $invoiceRelations = ['customer', 'service', 'employee', 'items'];

        if ($supportsInvoiceItemEmployees) {
            $invoiceRelations[] = 'items.employees';
        }

        if ($supportsInvoiceEmployees) {
            $invoiceRelations[] = 'employees';
        }

        $recentInvoices = SalonInvoice::with($invoiceRelations)
            ->latest()
            ->take(8)
            ->get();

        $latestInvoice = null;

        if ($this->latestInvoiceId) {
            $latestInvoice = SalonInvoice::with($invoiceRelations)->find($this->latestInvoiceId);
        }

        if (! $latestInvoice) {
            $latestInvoice = SalonInvoice::with($invoiceRelations)->latest()->first();
            $this->latestInvoiceId = $latestInvoice?->id;
        }

        $recentInvoiceViewUrls = [];
        $recentInvoicePrintUrls = [];

        foreach ($recentInvoices as $invoice) {
            $recentInvoiceViewUrls[$invoice->id] = $this->invoiceViewUrl($invoice);
            $recentInvoicePrintUrls[$invoice->id] = $this->invoicePrintUrl($invoice);
        }

        return view('livewire.salon.invoices', [
            'brandName' => self::BRAND_NAME_AR,
            'branches' => self::BRANCHES,
            'branchesEn' => self::BRANCHES_EN,
            'invoiceBranches' => collect(self::BRANCHES)->except('all')->all(),
            'customers' => $customers,
            'employees' => $employees,
            'supportsInvoiceItemEmployees' => $supportsInvoiceItemEmployees,
            'supportsInvoiceEmployees' => $supportsInvoiceEmployees,
            'services' => $services,
            'recentInvoices' => $recentInvoices,
            'latestInvoice' => $latestInvoice,
            'latestInvoicePrintUrl' => $this->invoicePrintUrl($latestInvoice),
            'latestInvoiceViewUrl' => $this->invoiceViewUrl($latestInvoice),
            'recentInvoiceViewUrls' => $recentInvoiceViewUrls,
            'recentInvoicePrintUrls' => $recentInvoicePrintUrls,
            'serviceSubtotal' => $this->lineItemsSubtotal(),
        ]);
    }

    public function selectInvoice(int $invoiceId): void
    {
        $this->latestInvoiceId = $invoiceId;
    }

    public function createInvoiceFromClient($customerId = null, $invoiceEmployeeIds = []): void
    {
        $this->customerId = (string) ($customerId ?? '');
        $this->invoiceEmployeeIds = collect($invoiceEmployeeIds)
            ->map(fn ($employeeId) => (int) $employeeId)
            ->filter(fn ($employeeId) => $employeeId > 0)
            ->unique()
            ->values()
            ->all();

        $this->createInvoice();
    }

    public function createInvoice(): void
    {
        $validatedItems = $this->validatedLineItems();

        $this->validate([
            'invoiceBranch' => 'required|in:avani,night_cassia',
            'customerId' => 'required|exists:customers,id',
            'paidAmount' => 'required|numeric|min:0',
            'paymentMethod' => 'required|in:cash,machine',
            'note' => 'nullable|string|max:2000',
        ]);

        $this->invoiceLanguage = 'en';

        $customer = Customer::findOrFail($this->customerId);
        $invoiceEmployeeIds = $this->validatedInvoiceEmployeeIds();
        $primaryService = SalonService::findOrFail($validatedItems[0]['service_id']);
        $serviceTotal = collect($validatedItems)->sum('line_total');

        $invoice = DB::transaction(function () use ($customer, $primaryService, $validatedItems, $serviceTotal, $invoiceEmployeeIds) {
            $invoice = SalonInvoice::create([
                'customer_id' => $customer->id,
                'salon_service_id' => $primaryService->id,
                'employee_id' => Auth::user()?->employee_id,
                'branch' => $this->invoiceBranch,
                'invoice_language' => 'en',
                'service_price' => $serviceTotal,
                'paid_amount' => $this->paidAmount,
                'payment_method' => $this->paymentMethod,
                'has_warranty' => false,
                'warranty_note' => null,
                'note' => filled($this->note) ? trim($this->note) : null,
                'invoice_date' => now(),
            ]);

            $invoice->update([
                'invoice_number' => $this->generateInvoiceNumber($invoice->id),
            ]);

            if ($this->supportsInvoiceEmployees()) {
                $invoice->employees()->sync($invoiceEmployeeIds);
            }

            foreach ($validatedItems as $item) {
                $service = SalonService::findOrFail($item['service_id']);

                $invoiceItem = SalonInvoiceItem::create([
                    'salon_invoice_id' => $invoice->id,
                    'salon_service_id' => $service->id,
                    'service_name' => $service->name,
                    'service_name_en' => $service->english_name,
                    'unit_price' => $item['price'],
                    'quantity' => 1,
                    'line_total' => $item['line_total'],
                ]);

                CustomerService::create([
                    'customer_id' => $customer->id,
                    'employee_id' => $invoiceEmployeeIds[0] ?? Auth::user()?->employee_id,
                    'service_name' => $service->name,
                    'note' => $this->buildCustomerServiceNote(
                        $invoice->fresh(),
                        $service->name,
                        (float) $item['line_total'],
                        $this->employeeNamesForIds($invoiceEmployeeIds, 'ar')
                    ),
                    'served_at' => $invoice->invoice_date,
                ]);
            }

            return $invoice;
        });

        $refreshRelations = ['customer', 'service', 'employee', 'items'];

        if ($this->supportsInvoiceItemEmployees()) {
            $refreshRelations[] = 'items.employees';
        }

        if ($this->supportsInvoiceEmployees()) {
            $refreshRelations[] = 'employees';
        }

        $whatsappResult = $this->sendInvoiceToWhatsapp($invoice->fresh($refreshRelations));

        $this->latestInvoiceId = $invoice->id;
        $this->resetForm();
        $this->dispatch(
            'toastr',
            type: $whatsappResult === true ? 'success' : 'warning',
            message: $whatsappResult === true
                ? __('Invoice created successfully.')
                : __('Invoice created, but WhatsApp delivery needs another attempt.')
        );
    }

    public function resendInvoiceWhatsapp(int $invoiceId): void
    {
        $relations = ['customer', 'service', 'employee', 'items'];

        if ($this->supportsInvoiceItemEmployees()) {
            $relations[] = 'items.employees';
        }

        if ($this->supportsInvoiceEmployees()) {
            $relations[] = 'employees';
        }

        $invoice = SalonInvoice::with($relations)->findOrFail($invoiceId);
        $this->latestInvoiceId = $invoice->id;

        $result = $this->sendInvoiceToWhatsapp($invoice);

        $this->dispatch(
            'toastr',
            type: $result === true ? 'success' : 'warning',
            message: $result === true
                ? __('Invoice sent via WhatsApp successfully.')
                : (string) $result
        );
    }

    private function sendInvoiceToWhatsapp(SalonInvoice $invoice)
    {
        $customerPhone = $invoice->customer?->phone;

        if (! $customerPhone) {
            $message = __('Customer does not have a phone number to receive the invoice on WhatsApp.');

            $invoice->update([
                'is_whatsapp_sent' => false,
                'whatsapp_error' => $message,
            ]);

            return $message;
        }

        $messageBody = $this->buildWhatsappInvoiceBody($invoice);

        $message = Message::create([
            'employee_id' => null,
            'text' => $messageBody,
            'recipient' => $customerPhone,
            'is_sent' => false,
        ]);

        $response = (new sendPendingMessagesByWhatsapp())->sendText($messageBody, $customerPhone);

        if ($response === true) {
            $message->update([
                'is_sent' => true,
                'error' => 'Sent by WhatsApp API',
            ]);

            $invoice->update([
                'is_whatsapp_sent' => true,
                'whatsapp_sent_at' => now(),
                'whatsapp_error' => null,
            ]);

            return true;
        }

        $message->update([
            'is_sent' => false,
            'error' => (string) $response,
        ]);

        $invoice->update([
            'is_whatsapp_sent' => false,
            'whatsapp_error' => Str::limit((string) $response, 255),
        ]);

        return (string) $response;
    }

    private function buildWhatsappInvoiceBody(SalonInvoice $invoice): string
    {
        return $this->buildEnglishWhatsappInvoiceBody($invoice);
    }

    private function buildArabicWhatsappInvoiceBody(SalonInvoice $invoice): string
    {
        $lines = [
            self::BRAND_NAME_AR,
            'الفرع: '.(self::BRANCHES[$invoice->branch] ?? $invoice->branch),
            'رقم الفاتورة: '.$invoice->invoice_number,
            'التاريخ: '.$invoice->invoice_date?->translatedFormat('j F Y - h:i A'),
            'اسم العميل: '.($invoice->customer->name ?: $invoice->customer->english_name),
            '------------------------------',
            'الخدمات:',
        ];

        foreach ($this->invoiceItemsCollection($invoice) as $index => $item) {
            $lines[] = ($index + 1).'. '.$item->service_name.' - '.number_format((float) $item->line_total, 2);
        }

        $invoiceEmployeeNames = $this->invoiceEmployeeNames($invoice, 'ar');

        if ($invoiceEmployeeNames !== []) {
            $lines[] = 'الموظفون الذين خدموا العميل: '.implode('، ', $invoiceEmployeeNames);
        }

        $lines[] = '------------------------------';
        $lines[] = 'إجمالي الخدمات: '.number_format((float) $invoice->service_price, 2);
        $lines[] = 'المبلغ المدفوع: '.number_format((float) $invoice->paid_amount, 2);
        $lines[] = 'طريقة الدفع: '.($invoice->payment_method === 'cash' ? 'كاش' : 'ماكينة');
        if ($invoice->note) {
            $lines[] = 'ملاحظات إضافية: '.$invoice->note;
        }

        $publicInvoiceUrl = $this->publicInvoiceViewUrl($invoice);
        if ($publicInvoiceUrl) {
            $lines[] = 'اضغط على الرابط التالي لعرض الفاتورة:';
            $lines[] = $publicInvoiceUrl;
        }

        $lines[] = 'شكرًا لاختياركم خدماتنا.';

        return implode(PHP_EOL, $lines);
    }

    private function buildEnglishWhatsappInvoiceBody(SalonInvoice $invoice): string
    {
        $lines = [
            self::BRAND_NAME_EN,
            'Branch: '.$this->branchEnglishLabel($invoice->branch),
            'Invoice No: '.$invoice->invoice_number,
            'Date: '.$invoice->invoice_date?->format('d M Y - h:i A'),
            'Customer: '.($invoice->customer->english_name ?: $invoice->customer->name),
            '------------------------------',
            'Services:',
        ];

        foreach ($this->invoiceItemsCollection($invoice) as $index => $item) {
            $serviceName = $item->service_name_en ?: $item->service_name;
            $lines[] = ($index + 1).'. '.$serviceName.' - '.number_format((float) $item->line_total, 2);
        }

        $invoiceEmployeeNames = $this->invoiceEmployeeNames($invoice, 'en');

        if ($invoiceEmployeeNames !== []) {
            $lines[] = 'Staff who served you: '.implode(', ', $invoiceEmployeeNames);
        }

        $lines[] = '------------------------------';
        $lines[] = 'Total: '.number_format((float) $invoice->service_price, 2);
        $lines[] = 'Paid: '.number_format((float) $invoice->paid_amount, 2);
        $lines[] = 'Payment Method: '.($invoice->payment_method === 'cash' ? 'Cash' : 'Machine');
        if ($invoice->note) {
            $lines[] = 'Notes: '.$invoice->note;
        }

        $publicInvoiceUrl = $this->publicInvoiceViewUrl($invoice);
        if ($publicInvoiceUrl) {
            $lines[] = 'Click the link below to view the invoice:';
            $lines[] = $publicInvoiceUrl;
        }

        $lines[] = 'Thank you for choosing our services.';

        return implode(PHP_EOL, $lines);
    }

    private function buildCustomerServiceNote(SalonInvoice $invoice, string $serviceName, float $lineTotal, array $employeeNames = []): string
    {
        $parts = [
            'رقم الفاتورة: '.$invoice->invoice_number,
            'الخدمة: '.$serviceName,
            'قيمة الخدمة: '.number_format($lineTotal, 2),
            'طريقة الدفع: '.($invoice->payment_method === 'cash' ? 'كاش' : 'ماكينة'),
        ];

        if ($employeeNames !== []) {
            $parts[] = 'الموظفون: '.implode('، ', $employeeNames);
        }

        if ($invoice->note) {
            $parts[] = 'ملاحظة: '.$invoice->note;
        }

        return implode(PHP_EOL, $parts);
    }

    private function generateInvoiceNumber(int $invoiceId): string
    {
        return 'INV-'.now()->format('Ymd').'-'.str_pad((string) $invoiceId, 4, '0', STR_PAD_LEFT);
    }

    private function invoicePrintUrl(?SalonInvoice $invoice): ?string
    {
        if (! $invoice) {
            return null;
        }

        return URL::temporarySignedRoute(
            'salon-invoices-pdf',
            now()->addHours(12),
            [
                'invoice' => $invoice->id,
            ]
        );
    }

    private function invoiceViewUrl(?SalonInvoice $invoice): ?string
    {
        if (! $invoice) {
            return null;
        }

        return URL::temporarySignedRoute(
            'salon-invoices-view',
            now()->addHours(12),
            [
                'invoice' => $invoice->id,
                'lang' => 'en',
            ]
        );
    }

    private function publicInvoiceViewUrl(?SalonInvoice $invoice): ?string
    {
        return $this->invoiceViewUrl($invoice);
    }

    private function lineItemsSubtotal(): float
    {
        return collect($this->lineItems)
            ->sum(fn ($item) => (float) ($item['price'] ?? 0));
    }

    private function validatedLineItems(): array
    {
        $items = collect($this->lineItems)
            ->map(fn ($item) => [
                'service_id' => isset($item['service_id']) ? (int) $item['service_id'] : 0,
                'price' => isset($item['price']) && $item['price'] !== '' ? (float) $item['price'] : 0,
            ])
            ->filter(fn ($item) => $item['service_id'] > 0)
            ->values();

        if ($items->isEmpty()) {
            $this->dispatch('toastr', type: 'warning', message: 'أضف خدمة واحدة على الأقل قبل إنشاء الفاتورة.');

            throw ValidationException::withMessages([
                'lineItems' => 'أضف خدمة واحدة على الأقل قبل إنشاء الفاتورة.',
            ]);
        }

        $serviceIds = $items->pluck('service_id');

        if ($serviceIds->count() !== $serviceIds->unique()->count()) {
            throw ValidationException::withMessages([
                'lineItems' => 'لا يمكن تكرار نفس الخدمة داخل الفاتورة الواحدة.',
            ]);
        }

        return $items->map(function ($item) {
            $service = SalonService::findOrFail($item['service_id']);

            return [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'service_name_en' => $service->english_name,
                'price' => $item['price'] > 0 ? $item['price'] : (float) $service->price,
                'line_total' => $item['price'] > 0 ? $item['price'] : (float) $service->price,
            ];
        })->all();
    }

    private function invoiceItemsCollection(SalonInvoice $invoice)
    {
        return $invoice->items->isNotEmpty()
            ? $invoice->items
            : collect([(object) [
                'service_name' => $invoice->service?->name,
                'service_name_en' => $invoice->service?->english_name,
                'line_total' => $invoice->service_price,
            ]]);
    }

    private function branchEnglishLabel(string $branch): string
    {
        return self::BRANCHES_EN[$branch] ?? ucfirst(str_replace('_', ' ', $branch));
    }

    private function emptyLineItem(): array
    {
        return [
            'service_id' => '',
            'service_name' => '',
            'service_name_en' => '',
            'price' => '',
        ];
    }

    private function employeeNamesForIds(array $employeeIds, string $language = 'ar'): array
    {
        if ($employeeIds === []) {
            return [];
        }

        return Employee::query()
            ->whereIn('id', $employeeIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Employee $employee) => $this->employeeDisplayTitle($employee, $language))
            ->filter()
            ->values()
            ->all();
    }

    private function supportsInvoiceItemEmployees(): bool
    {
        return Schema::hasTable('salon_invoice_item_employee');
    }

    private function validatedInvoiceEmployeeIds(): array
    {
        return Employee::query()
            ->whereIn('id', collect($this->invoiceEmployeeIds)
                ->map(fn ($employeeId) => (int) $employeeId)
                ->filter(fn ($employeeId) => $employeeId > 0)
                ->unique()
                ->values()
                ->all())
            ->pluck('id')
            ->map(fn ($employeeId) => (int) $employeeId)
            ->all();
    }

    private function invoiceEmployeeNames(SalonInvoice $invoice, ?string $language = null): array
    {
        if (! method_exists($invoice, 'relationLoaded') || ! $invoice->relationLoaded('employees')) {
            return [];
        }

        $language = $language ?: ($invoice->invoice_language ?: 'ar');

        return $invoice->employees
            ->map(fn (Employee $employee) => $this->employeeDisplayTitle($employee, $language))
            ->filter()
            ->values()
            ->all();
    }

    private function employeeDisplayTitle(Employee $employee, string $language = 'ar'): string
    {
        if ($language === 'en') {
            return $employee->job_title_en ?: $employee->job_title ?: ($employee->full_name ?: $employee->first_name);
        }

        return $employee->job_title ?: $employee->job_title_en ?: ($employee->full_name ?: $employee->first_name);
    }

    private function supportsInvoiceEmployees(): bool
    {
        return Schema::hasTable('salon_invoice_employee');
    }

    private function resetForm(): void
    {
        $this->reset('paidAmount', 'note', 'invoiceEmployeeIds');
        $this->invoiceBranch = 'night_cassia';
        $this->invoiceLanguage = 'en';
        $this->paymentMethod = 'cash';
        $this->lineItems = [$this->emptyLineItem()];
    }
}
