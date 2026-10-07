<div>
  @section('title', 'Invoices')

  @push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <style>
      .invoice-shell {
        background:
          radial-gradient(circle at top left, rgba(115, 103, 240, 0.16), transparent 26%),
          radial-gradient(circle at top right, rgba(40, 199, 111, 0.12), transparent 22%),
          linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 249, 255, 0.98));
        border: 1px solid rgba(115, 103, 240, 0.14);
        box-shadow: 0 24px 60px rgba(34, 41, 47, 0.1);
      }

      .invoice-hero {
        border-radius: 1rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #7367f0, #8f7cff 58%, #5d53d5);
        color: #fff;
      }

      .invoice-badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        font-size: 0.85rem;
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
      }

      .brand-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
      }

      .brand-mark {
        width: 78px;
        height: 78px;
        object-fit: contain;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 1rem;
        padding: 0.45rem;
        box-shadow: 0 12px 24px rgba(26, 31, 57, 0.14);
      }

      .group-mark {
        width: 28px;
        height: 28px;
        object-fit: contain;
      }

      .brand-title {
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1.1;
        margin: 0;
      }

      .brand-subtitle {
        font-size: 1rem;
        font-weight: 600;
        opacity: 0.9;
        margin-top: 0.2rem;
      }

      .brand-location {
        font-size: 0.95rem;
        opacity: 0.82;
        margin-top: 0.45rem;
      }

      .invoice-info-card {
        border: 1px solid rgba(115, 103, 240, 0.16);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.82);
        padding: 1rem 1.1rem;
        height: 100%;
      }

      .invoice-info-card.welcome-card {
        background:
          radial-gradient(circle at top right, rgba(212, 184, 94, 0.14), transparent 34%),
          linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 250, 240, 0.96));
        border-color: rgba(212, 184, 94, 0.28);
      }

      .invoice-info-card .label {
        color: #8a8ca8;
        font-size: 0.82rem;
        margin-bottom: 0.35rem;
      }

      .welcome-kicker {
        font-size: 0.92rem;
        font-weight: 700;
        color: #b48b2f;
        margin-bottom: 0.3rem;
      }

      .welcome-copy {
        color: #8a8ca8;
        font-size: 0.9rem;
        margin-top: 0.4rem;
      }

      .hospitality-note {
        border: 1px dashed rgba(212, 184, 94, 0.38);
        border-radius: 1rem;
        padding: 0.95rem 1rem;
        background: linear-gradient(180deg, rgba(255, 248, 232, 0.92), rgba(255, 255, 255, 0.96));
        color: #6d5a2a;
      }

      .hospitality-note strong {
        display: block;
        margin-bottom: 0.3rem;
        color: #9b7a26;
      }

      .service-line {
        border: 1px dashed rgba(115, 103, 240, 0.24);
        border-radius: 1rem;
        padding: 0.9rem;
        background: rgba(115, 103, 240, 0.03);
      }

      .invoice-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
      }

      .invoice-summary-tile {
        border-radius: 1rem;
        padding: 1rem;
        background: linear-gradient(180deg, rgba(115, 103, 240, 0.08), rgba(115, 103, 240, 0.03));
        border: 1px solid rgba(115, 103, 240, 0.12);
      }

      .invoice-summary-tile .value {
        font-size: 1.15rem;
        font-weight: 700;
        color: #2d2f45;
      }

      .invoice-summary-tile .meta {
        color: #8a8ca8;
        font-size: 0.82rem;
      }

      .invoice-table-clean thead th {
        border-bottom-width: 1px;
        color: #7a7d9c;
        font-size: 0.85rem;
        font-weight: 600;
        background: rgba(115, 103, 240, 0.04);
      }

      .invoice-table-clean tbody td {
        vertical-align: middle;
      }

      .invoice-status-box {
        border-radius: 1rem;
        padding: 1rem 1.1rem;
        border: 1px dashed rgba(115, 103, 240, 0.25);
        background: rgba(115, 103, 240, 0.04);
      }

      .invoice-watermark {
        position: absolute;
        inset-inline-end: 1.25rem;
        inset-block-end: 1rem;
        font-size: 4rem;
        font-weight: 800;
        color: rgba(115, 103, 240, 0.05);
        pointer-events: none;
        user-select: none;
      }

      .select2-container {
        width: 100% !important;
      }

      .select2-container--default .select2-selection--single {
        min-height: calc(2.25rem + 2px);
        border-color: #dbdade;
        display: flex;
        align-items: center;
      }

      .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-inline-start: 0.75rem;
        padding-inline-end: 2rem;
      }

      .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        inset-inline-end: 0.35rem;
      }

      .select2-container--default .select2-selection--multiple {
        min-height: calc(2.25rem + 2px);
        border-color: #dbdade;
      }

      .select2-container--default.select2-container--focus .select2-selection--single,
      .select2-container--default.select2-container--focus .select2-selection--multiple,
      .select2-container--default.select2-container--open .select2-selection--multiple,
      .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #7367f0;
      }

      @media (max-width: 991px) {
        .invoice-summary-grid {
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }
      }

      @media (max-width: 575px) {
        .invoice-summary-grid {
          grid-template-columns: 1fr;
        }
      }

      @media print {
        @page {
          size: 160mm 160mm;
          margin: 6mm;
        }

        html,
        body {
          margin: 0 !important;
          padding: 0 !important;
          background: #fff !important;
          width: auto !important;
          height: auto !important;
          overflow: visible !important;
        }

        .no-print,
        .btn,
        .card-header .text-muted {
          display: none !important;
        }

        .row.g-4,
        .row.g-3,
        .d-flex,
        .invoice-summary-grid {
          gap: 0.45rem !important;
        }

        .col-12.col-xl-7 {
          flex: 0 0 100% !important;
          max-width: 100% !important;
          width: 100% !important;
        }

        #invoice-print-area {
          position: static !important;
          width: 148mm !important;
          max-width: 148mm !important;
          margin: 0 auto !important;
          padding: 0 !important;
          background: #fff !important;
          color: #000 !important;
          box-shadow: none !important;
          border: 0 !important;
          overflow: hidden !important;
          break-inside: avoid-page;
          page-break-inside: avoid;
        }

        #invoice-print-area .card-body {
          padding: 7mm !important;
        }

        #invoice-print-area .invoice-hero {
          padding: 0.75rem !important;
          margin-bottom: 0.65rem !important;
          background: #f3f4ff !important;
          color: #222 !important;
        }

        #invoice-print-area .invoice-badge-soft {
          background: rgba(115, 103, 240, 0.12) !important;
          color: #4b4f6c !important;
          font-size: 0.72rem !important;
        }

        #invoice-print-area h2,
        #invoice-print-area .fs-5 {
          font-size: 1.2rem !important;
        }

        #invoice-print-area .small,
        #invoice-print-area .label,
        #invoice-print-area .meta,
        #invoice-print-area td,
        #invoice-print-area th,
        #invoice-print-area div,
        #invoice-print-area span {
          font-size: 11px !important;
        }

        #invoice-print-area .invoice-info-card,
        #invoice-print-area .invoice-summary-tile {
          padding: 0.6rem !important;
          border-radius: 0.75rem !important;
        }

        #invoice-print-area .invoice-summary-grid {
          grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
          margin-bottom: 0.65rem !important;
        }

        #invoice-print-area .table-responsive {
          overflow: visible !important;
          margin-bottom: 0.65rem !important;
        }

        #invoice-print-area .invoice-table-clean {
          margin-bottom: 0 !important;
        }

        #invoice-print-area .invoice-table-clean th,
        #invoice-print-area .invoice-table-clean td {
          padding: 0.45rem !important;
        }

        #invoice-print-area .mb-4 {
          margin-bottom: 0.65rem !important;
        }

        #invoice-print-area .mt-2,
        #invoice-print-area .mt-1,
        #invoice-print-area .mt-3 {
          margin-top: 0.2rem !important;
        }

        #invoice-print-area .invoice-watermark {
          font-size: 2.8rem !important;
          inset-inline-end: 0.5rem !important;
          inset-block-end: 0.25rem !important;
        }
      }
    </style>
  @endpush

  <div class="row g-4">
    <div class="col-12 col-xl-5 no-print">
      <div class="card no-print">
        <div class="card-header">
          <h5 class="mb-1">{{ __('Create Invoice') }}</h5>
          <p class="text-muted mb-0">اختر الفرع والعميل والخدمات، ثم ستبقى الفاتورة جاهزة للعرض والطباعة والإرسال.</p>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">{{ __('Branch') }}</label>
            <select wire:model.live="invoiceBranch" class="form-select @error('invoiceBranch') is-invalid @enderror">
              @foreach ($invoiceBranches as $key => $branchName)
                <option value="{{ $key }}">{{ $branchName }}</option>
              @endforeach
            </select>
            @error('invoiceBranch')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __('Customer') }}</label>
            <div wire:ignore>
              <select id="invoiceCustomerSelect" class="form-select customer-search-select @error('customerId') is-invalid @enderror">
                <option value="">{{ __('Select..') }}</option>
                @foreach ($customers as $customer)
                  <option value="{{ $customer->id }}" @selected((string) $customerId === (string) $customer->id)>
                    {{ $customer->name }}
                    @if ($customer->english_name)
                      {{ ' / '.$customer->english_name }}
                    @endif
                    {{ $customer->phone ? ' - '.$customer->phone : '' }}
                  </option>
                @endforeach
              </select>
            </div>
            @error('customerId')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label mb-0">الخدمات داخل الفاتورة</label>
              <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addServiceRow">
                <span class="ti ti-plus me-1"></span>إضافة خدمة
              </button>
            </div>
            <div class="small text-muted mb-2">يمكنك إضافة أكثر من خدمة لنفس العميل داخل نفس الفاتورة حتى 15 خدمة.</div>
            @foreach ($lineItems as $index => $item)
              <div class="service-line mb-3" wire:key="service-line-{{ $index }}-{{ $item['service_id'] ?? 'none' }}">
                <div class="row g-3 align-items-end">
                  <div class="col-md-7">
                    <label class="form-label">الخدمة {{ $index + 1 }}</label>
                    <select wire:model.live="lineItems.{{ $index }}.service_id" class="form-select">
                      <option value="">{{ __('Select..') }}</option>
                      @foreach ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }} - {{ number_format((float) $service->price, 2) }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">السعر</label>
                    <input wire:model.defer="lineItems.{{ $index }}.price" type="number" min="0" step="0.01" class="form-control">
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100" wire:click="removeServiceRow({{ $index }})">
                      <span class="ti ti-trash"></span>
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
            @error('lineItems')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">إجمالي الخدمات</label>
              <input type="text" class="form-control" value="{{ number_format((float) $serviceSubtotal, 2) }}" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('Paid Amount') }}</label>
              <input wire:model.defer="paidAmount" type="number" min="0" step="0.01" class="form-control @error('paidAmount') is-invalid @enderror">
              @error('paidAmount')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">{{ __('Payment Method') }}</label>
            <select wire:model.defer="paymentMethod" class="form-select @error('paymentMethod') is-invalid @enderror">
              <option value="cash">{{ __('Cash') }}</option>
              <option value="machine">{{ __('Machine') }}</option>
            </select>
            @error('paymentMethod')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mt-3">
            <label class="form-label">{{ __('Note') }}</label>
            <textarea wire:model.defer="note" rows="3" dir="auto" style="unicode-bidi: plaintext;" class="form-control @error('note') is-invalid @enderror"></textarea>
            @error('note')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mt-3">
            <label class="form-label">الموظفون الذين خدموا العميل</label>
            <div wire:ignore>
              <select id="invoiceEmployeesSelect" class="form-select invoice-employee-search-select" multiple @disabled(! $supportsInvoiceEmployees)>
                @foreach ($employees as $employee)
                  <option value="{{ $employee->id }}" @selected(in_array($employee->id, $invoiceEmployeeIds ?? []))>
                    {{ $employee->job_title ?: $employee->job_title_en ?: ($employee->full_name ?: $employee->first_name) }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="small text-muted mt-1">
              {{ $supportsInvoiceEmployees ? 'اختر موظفًا واحدًا أو أكثر ليظهروا في آخر الفاتورة ورسالة الواتساب.' : 'يلزم تشغيل المايغريشن الجديدة أولًا لتفعيل حفظ الموظفين داخل الفاتورة.' }}
            </div>
          </div>

          <div class="d-grid mt-4">
            <button onclick="window.createSalonInvoice && window.createSalonInvoice()" type="button" class="btn btn-primary">
              <span class="ti ti-file-invoice me-1"></span>{{ __('Create and Send Invoice') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="card invoice-shell mb-4 position-relative overflow-hidden" id="invoice-print-area">
        <div class="card-body p-4 p-lg-5">
          @if ($latestInvoice)
            @php
              $invoiceItems = $latestInvoice->items->isNotEmpty()
                ? $latestInvoice->items
                : collect([(object) [
                    'service_name' => $latestInvoice->service?->name,
                    'service_name_en' => $latestInvoice->service?->english_name,
                    'line_total' => $latestInvoice->service_price,
                  ]]);
            @endphp
            <div class="invoice-hero mb-4">
              <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                  <div class="invoice-badge-soft mb-3">
                    <img src="{{ asset('assets/img/logo/logo_128.png') }}" alt="Omar Mal Group" class="group-mark">
                    <span>Omar Mal Group</span>
                  </div>
                  <div class="brand-row">
                    <div>
                      <h2 class="brand-title text-white">Hurghada Salon</h2>
                      <div class="brand-subtitle">Professional Beauty Services</div>
                      <div class="brand-location">Branch: {{ $branchesEn[$latestInvoice->branch] ?? $latestInvoice->branch }}</div>
                    </div>
                  </div>
                  <div class="opacity-75 no-print mt-2">{{ __('Invoice Preview') }}</div>
                </div>

                <div class="text-lg-end">
                  <div class="invoice-badge-soft mb-2">
                    <i class="ti ti-receipt-2"></i>
                    <span>{{ __('Invoice No.') }} {{ $latestInvoice->invoice_number }}</span>
                  </div>
                  <div class="small opacity-75">{{ $latestInvoice->invoice_date?->format('d M Y - h:i A') }}</div>
                  <div class="mt-3">
                    <img src="{{ asset('assets/img/logo/LOGOHI.png') }}" alt="Hurghada Salon" class="brand-mark">
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 no-print">
              <div>
                <h5 class="mb-1 no-print">{{ __('The latest invoice remains available here for review and printing.') }}</h5>
                <div class="text-muted">
                  {{ $latestInvoice->customer?->english_name ?: $latestInvoice->customer?->name }} / {{ $invoiceItems->count() }} services
                </div>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-success" wire:click="resendInvoiceWhatsapp({{ $latestInvoice->id }})">
                  <span class="ti ti-brand-whatsapp me-1"></span>{{ __('Resend via WhatsApp') }}
                </button>
                @if ($latestInvoicePrintUrl)
                  <a href="{{ $latestInvoicePrintUrl }}" target="_blank" class="btn btn-outline-primary">
                    <span class="ti ti-printer me-1"></span>{{ __('Print Invoice') }}
                  </a>
                @endif
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <div class="invoice-info-card welcome-card">
                  <div class="welcome-kicker">Welcome to Hurghada Salon</div>
                  <div class="fw-bold fs-5">
                    {{ $latestInvoice->customer?->english_name ?: $latestInvoice->customer?->name }}
                  </div>
                  <div class="mt-2">{{ $latestInvoice->customer?->phone ?: __('No phone number') }}</div>
                  <div class="welcome-copy">We are delighted to serve you and hope you enjoy a comfortable experience with us.</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="invoice-info-card">
                  <div class="label">{{ __('Service') }}</div>
                  <div class="fw-bold fs-5">{{ $invoiceItems->count() }} services in this invoice</div>
                  <div class="mt-2">{{ __('Created by') }}: {{ $latestInvoice->employee?->full_name ?: $latestInvoice->created_by }}</div>
                  <div class="text-muted mt-1">Branch: {{ $branchesEn[$latestInvoice->branch] ?? $latestInvoice->branch }}</div>
                </div>
              </div>
            </div>

            <div class="hospitality-note mb-4">
              <strong>Your feedback matters</strong>
              If you have any complaint or note about our services, please contact us directly and we will be happy to assist you.
            </div>

            <div class="invoice-summary-grid mb-4">
              <div class="invoice-summary-tile">
                <div class="meta">{{ __('Service Price') }}</div>
                <div class="value">{{ number_format((float) $latestInvoice->service_price, 2) }}</div>
              </div>
              <div class="invoice-summary-tile">
                <div class="meta">{{ __('Paid Amount') }}</div>
                <div class="value">{{ number_format((float) $latestInvoice->paid_amount, 2) }}</div>
              </div>
              <div class="invoice-summary-tile">
                <div class="meta">{{ __('Payment Method') }}</div>
                <div class="value">{{ $latestInvoice->payment_method === 'cash' ? __('Cash') : __('Machine') }}</div>
              </div>
            </div>

            <div class="table-responsive mb-4">
              <table class="table invoice-table-clean">
                <thead>
                  <tr>
                    <th>{{ __('Service Name') }}</th>
                    <th>Branch</th>
                    <th>{{ __('Service Price') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($invoiceItems as $item)
                    <tr>
                      <td class="fw-semibold">
                        <div>{{ $item->service_name_en ?: $item->service_name }}</div>
                      </td>
                      <td>{{ $branchesEn[$latestInvoice->branch] ?? $latestInvoice->branch }}</td>
                      <td>{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="row g-3">
              @if (filled($latestInvoice->note))
                <div class="col-md-6">
                  <div class="invoice-info-card">
                    <div class="label">{{ __('Additional Note') }}</div>
                    @php
                      $latestInvoiceNote = (string) $latestInvoice->note;
                      $latestInvoiceNoteIsArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $latestInvoiceNote);
                    @endphp
                    <div dir="{{ $latestInvoiceNoteIsArabic ? 'rtl' : 'ltr' }}" style="white-space: pre-wrap; unicode-bidi: plaintext; text-align: {{ $latestInvoiceNoteIsArabic ? 'right' : 'left' }}; font-family: {{ $latestInvoiceNoteIsArabic ? 'Tahoma, Segoe UI, Arial, sans-serif' : 'inherit' }};">{{ $latestInvoiceNote }}</div>
                  </div>
                </div>
              @endif
              @if ($supportsInvoiceEmployees && $latestInvoice->relationLoaded('employees') && $latestInvoice->employees->isNotEmpty())
                <div class="col-12">
                  <div class="invoice-info-card">
                    <div class="label">Staff who served the customer</div>
                    <div>{{ $latestInvoice->employees->map(fn ($employee) => $employee->job_title_en ?: $employee->job_title ?: ($employee->full_name ?: $employee->first_name))->filter()->implode(', ') }}</div>
                  </div>
                </div>
              @endif
              <div class="col-12">
                <div class="invoice-status-box no-print">
                  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                      <div class="label">{{ __('WhatsApp Status') }}</div>
                      <div class="fw-semibold {{ $latestInvoice->is_whatsapp_sent ? 'text-success' : 'text-warning' }}">
                        {{ $latestInvoice->is_whatsapp_sent ? __('Sent successfully') : __('Not sent') }}
                      </div>
                    </div>
                    <div class="no-print">
                      <button type="button" class="btn btn-sm btn-outline-success" wire:click="resendInvoiceWhatsapp({{ $latestInvoice->id }})">
                        <span class="ti ti-refresh me-1"></span>{{ __('Resend via WhatsApp') }}
                      </button>
                    </div>
                  </div>
                  @if ($latestInvoice->whatsapp_error)
                    <div class="small mt-3 text-muted">{{ $latestInvoice->whatsapp_error }}</div>
                  @endif
                </div>
              </div>
            </div>

            <div class="invoice-watermark">HURGHADA</div>
          @else
            <div class="text-center py-5">
              <h5 class="mb-2">{{ __('No invoices yet.') }}</h5>
              <p class="text-muted mb-0">{{ __('Create the first invoice and it will appear here instantly.') }}</p>
            </div>
          @endif
        </div>
      </div>

      <div class="card no-print">
        <div class="card-header d-flex justify-content-between align-items-center gap-2">
          <h5 class="mb-0">{{ __('Recent Invoices') }}</h5>
          <div class="text-muted small">{{ __('Select any invoice to review it or resend it.') }}</div>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>{{ __('Invoice No.') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Service') }}</th>
                <th>{{ __('Paid Amount') }}</th>
                <th>{{ __('WhatsApp Status') }}</th>
                <th>{{ __('Actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentInvoices as $invoice)
                <tr>
                  <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                  <td>{{ $invoice->customer?->english_name ?: $invoice->customer?->name }}</td>
                  <td>{{ $invoice->items->count() > 0 ? ($invoice->items->count().' خدمات') : $invoice->service?->name }}</td>
                  <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                  <td>
                    <span class="badge {{ $invoice->is_whatsapp_sent ? 'bg-label-success' : 'bg-label-warning' }}">
                      {{ $invoice->is_whatsapp_sent ? __('Sent successfully') : __('Not sent') }}
                    </span>
                  </td>
                  <td>
                    <div class="d-flex gap-2">
                      <button wire:click="selectInvoice({{ $invoice->id }})" type="button" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-eye"></i>
                      </button>
                      <button wire:click="resendInvoiceWhatsapp({{ $invoice->id }})" type="button" class="btn btn-sm btn-outline-success">
                        <i class="ti ti-brand-whatsapp"></i>
                      </button>
                      @if (!empty($recentInvoicePrintUrls[$invoice->id]))
                        <a href="{{ $recentInvoicePrintUrls[$invoice->id] }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                          <i class="ti ti-printer"></i>
                        </a>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-4">{{ __('No invoices yet.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@push('custom-scripts')
  <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
  <script>
    document.addEventListener('livewire:init', () => {
      const initializeInvoiceCustomerSelect = () => {
        const customerSelect = $('#invoiceCustomerSelect');

        if (!customerSelect.length) {
          return;
        }

        if (customerSelect.hasClass('select2-hidden-accessible')) {
          customerSelect.select2('destroy');
        }

        customerSelect.select2({
          placeholder: '{{ __('Select..') }}',
          allowClear: true,
          width: '100%'
        });

        customerSelect.val(@this.get('customerId') || '').trigger('change.select2');

      };

      const initializeInvoiceEmployeeSelects = () => {
        $('.employee-search-select').each(function () {
          const employeeSelect = $(this);
          const lineIndex = employeeSelect.data('line-index');

          employeeSelect.off('change.invoiceEmployees');

          if (employeeSelect.hasClass('select2-hidden-accessible')) {
            employeeSelect.select2('destroy');
          }

          employeeSelect.select2({
            placeholder: 'اختر الموظفين',
            width: '100%'
          });

          employeeSelect.on('change.invoiceEmployees', function () {
            @this.set(`lineItems.${lineIndex}.employee_ids`, $(this).val() || []);
          });
        });
      };

      const initializeInvoiceSelects = () => {
        initializeInvoiceCustomerSelect();
        initializeInvoiceEmployeeSelects();
      };

      initializeInvoiceSelects();

      Livewire.hook('morphed', ({ component }) => {
        if (component.name === 'salon.invoices') {
          initializeInvoiceSelects();
        }
      });
    });
  </script>
  <script>
    document.addEventListener('livewire:init', () => {
      const initializeInvoiceEmployeesSelectOverride = () => {
        const employeeSelect = $('#invoiceEmployeesSelect');

        if (!employeeSelect.length) {
          return;
        }

        if (employeeSelect.hasClass('select2-hidden-accessible')) {
          employeeSelect.select2('destroy');
        }

        employeeSelect.select2({
          placeholder: 'اختر الموظفين',
          width: '100%'
        });

        employeeSelect.val(@this.get('invoiceEmployeeIds') || []).trigger('change.select2');
      };

      window.createSalonInvoice = () => {
        const customerId = $('#invoiceCustomerSelect').val() || '';
        const invoiceEmployeeIds = $('#invoiceEmployeesSelect').val() || [];

        @this.call('createInvoiceFromClient', customerId, invoiceEmployeeIds);
      };

      initializeInvoiceEmployeesSelectOverride();

      Livewire.hook('morphed', ({ component }) => {
        if (component.name === 'salon.invoices') {
          initializeInvoiceEmployeesSelectOverride();
        }
      });
    });
  </script>
@endpush
