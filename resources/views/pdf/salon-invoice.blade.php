<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <title>{{ $invoice->invoice_number }}</title>
  @php
    $invoiceItems = $invoice->items->isNotEmpty()
      ? $invoice->items
      : collect([(object) [
          'service_name' => $invoice->service?->name,
          'service_name_en' => $invoice->service?->english_name,
          'line_total' => $invoice->service_price,
        ]]);

    $branchName = $branches[$invoice->branch] ?? ucfirst(str_replace('_', ' ', (string) $invoice->branch));
    $customerName = $invoice->customer?->english_name ?: $invoice->customer?->name ?: 'Guest Customer';
    $creator = $invoice->employee;
    $createdBy = $creator?->job_title_en ?: $creator?->full_name ?: ($invoice->created_by ?: 'System');
    $paymentMethod = $invoice->payment_method === 'cash' ? 'Cash' : 'Card / Machine';
    $legalCompanyName = 'HURGHADA TRADING L.L.C';
    $taxRegistrationNumber = '104833256100003';
    $logoPath = public_path('assets/img/logo/invois.png');
    $logoUrl = null;
    if (is_file($logoPath) && ($logoContents = file_get_contents($logoPath)) !== false) {
        $logoUrl = 'data:image/png;base64,'.base64_encode($logoContents);
    }
    $qrPayload = implode("\n", [
        $legalCompanyName,
        'TRN: '.$taxRegistrationNumber,
        'Invoice: '.$invoice->invoice_number,
        'Date: '.$invoice->invoice_date?->format('d M Y - h:i A'),
        'Customer: '.$customerName,
        'Total: AED '.number_format((float) $invoice->service_price, 2),
        'Paid: AED '.number_format((float) $invoice->paid_amount, 2),
    ]);
    $invoiceQrImage = null;

    if (class_exists(\BaconQrCode\Writer::class)) {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(78, 1),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );

        $svgString = (new \BaconQrCode\Writer($renderer))->writeString($qrPayload);
        $invoiceQrImage = 'data:image/svg+xml;base64,' . base64_encode($svgString);
    }
  @endphp
  <style>
    @page {
      size: A5 portrait;
      margin: 6mm;
    }

    body {
      margin: 0;
      padding: 0;
      color: #1d2117;
      font-family: DejaVu Sans, sans-serif;
      font-size: 8px;
      line-height: 1.2;
      page-break-after: avoid;
    }

    .header {
      background: #fff9ec;
      color: #1d2117;
      border: 1px solid #d8b865;
      border-top: 7px solid #b99335;
      border-radius: 12px;
      padding: 8px 10px;
      margin-bottom: 5px;
      page-break-inside: avoid;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      page-break-inside: avoid;
    }

    .brand {
      color: #244315;
      font-size: 20px;
      font-weight: 700;
    }

    .brand-wrap {
      width: 100%;
      border-collapse: collapse;
    }

    .logo-cell {
      width: 112px;
      vertical-align: middle;
      text-align: center;
      padding: 0 20px;
    }

    .brand-logo {
      width: 65px;
      height: 65px;
      object-fit: contain;
      border: 1px solid #ead9a7;
      border-radius: 9px;
      padding: 3px;
      background: #fff;
      margin-left: 15px;
    }

    .brand-logo-fallback {
      width: 78px;
      height: 78px;
      border: 1px solid #ead9a7;
      border-radius: 9px;
      color: #2c4b1b;
      font-size: 18px;
      font-weight: 800;
      text-align: center;
      line-height: 78px;
      background: #fffdf3;
    }

    .muted {
      color: #6b7280;
    }

    .header .muted {
      color: #6d735f;
    }

    .legal-line {
      margin-top: 5px;
      font-size: 8px;
      color: #8b6b17;
      font-weight: 700;
    }

    .qr-box {
      display: block;
      width: 140px;
      margin: 8px auto 0;
      padding: 7px;
      border-radius: 10px;
      border: 1px solid #ead9a7;
      background: #fff;
      text-align: center;
    }

    .qr-section {
      text-align: center;
      margin-top: 8px;
      margin-bottom: 0;
      page-break-inside: avoid;
    }

    .qr-title {
      font-size: 8px;
      font-weight: 700;
      color: #2c4b1b;
      margin-top: 4px;
    }

    .qr-box svg,
    .qr-box img {
      width: 120px;
      height: 120px;
      display: block;
      margin: 0 auto;
    }

    .invoice-no {
      font-size: 14px;
      font-weight: 700;
      margin: 3px 0;
    }

    .grid td {
      width: 50%;
      padding: 2px;
      vertical-align: top;
    }

    .panel {
      border: 1px solid #e4d4a0;
      border-radius: 10px;
      padding: 5px 6px;
      background: #fffdf7;
      min-height: 40px;
    }

    .label {
      color: #8b6b17;
      font-size: 8px;
      text-transform: uppercase;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .value {
      color: #172033;
      font-size: 12px;
      font-weight: 700;
    }

    .items {
      margin: 3px 0;
      border: 1px solid #e4d4a0;
      border-radius: 10px;
      overflow: hidden;
    }

    .items th,
    .items td {
      padding: 5px 6px;
      border-bottom: 1px solid #eceef8;
      text-align: left;
    }

    .items th {
      background: #244315;
      color: #fff;
      font-size: 8px;
      text-transform: uppercase;
    }

    .items tr:last-child td {
      border-bottom: 0;
    }

    .right {
      text-align: right;
    }

    .summary td {
      padding: 2px;
      vertical-align: top;
    }

    .total-box {
      position: relative;
      border: 1px solid #e4d4a0;
      border-radius: 10px;
      overflow: hidden;
    }

    .total-row {
      padding: 3px 5px;
      border-bottom: 1px solid #eceef8;
    }

    .total-row.last {
      border-bottom: 0;
      background: #fff5d6;
      color: #8b6b17;
      font-size: 12px;
      font-weight: 700;
    }

    .footer {
      margin-top: 3px;
      text-align: center;
      color: #8b8775;
      font-size: 8px;
    }

    .notice-grid {
      width: 100%;
      margin-top: 3px;
      border-collapse: separate;
      border-spacing: 3px 0;
    }

    .notice-box {
      border: 1px solid #e4d4a0;
      border-radius: 10px;
      background: #fffaf0;
      padding: 5px 6px;
      vertical-align: top;
      color: #51431f;
    }

    .notice-title {
      color: #8b6b17;
      font-size: 8px;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 4px;
    }

    .notice-phone {
      color: #244315;
      font-size: 12px;
      font-weight: 700;
      margin-top: 4px;
    }
  </style>
</head>
<body>
  <div class="header">
    <table>
      <tr>
        <td style="width: 40%;">
          <div class="brand">Hurghada Salon</div>
          <div class="muted">Professional Beauty Services</div>
          <div class="muted">Branch: {{ $branchName }}</div>
          <div class="legal-line">{{ $legalCompanyName }} | TRN {{ $taxRegistrationNumber }}</div>
        </td>
        <td class="logo-cell" style="width: 20%;">
          @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="Hurghada Salon" class="brand-logo">
          @else
            <div class="brand-logo-fallback">H</div>
          @endif
        </td>
        <td style="width: 40%; text-align: right;">
          <div class="muted">Invoice</div>
          <div class="invoice-no">{{ $invoice->invoice_number }}</div>
          <div class="muted">{{ $invoice->invoice_date?->format('d M Y - h:i A') }}</div>
        </td>
      </tr>
    </table>
  </div>

  <table class="grid">
    <tr>
      <td>
        <div class="panel">
          <div class="label">Customer</div>
          <div class="value">{{ $customerName }}</div>
          <div class="muted">{{ $invoice->customer?->phone ?: 'No phone number' }}</div>
        </div>
      </td>
      <td>
        <div class="panel">
          <div class="label">Invoice Details</div>
          <div class="value">{{ $invoiceItems->count() }} service{{ $invoiceItems->count() === 1 ? '' : 's' }}</div>
          <div class="muted">Created by: {{ $createdBy }}</div>
        </div>
      </td>
    </tr>
  </table>

  <table class="items">
    <thead>
      <tr>
        <th>Service</th>
        <th>Branch</th>
        <th class="right">Price</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($invoiceItems as $item)
        <tr>
          <td>{{ $item->service_name_en ?: $item->service_name }}</td>
          <td>{{ $branchName }}</td>
          <td class="right">AED {{ number_format((float) $item->line_total, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table class="summary">
    <tr>
      <td style="width: 48%;">
        <div class="panel">
          <div class="label">Customer Care</div>
          If you have any complaint or note about our services, please contact us directly.
          @if (filled($invoice->note))
            <div style="margin-top: 6px;">
              <strong>Note:</strong>
              <div dir="{{ preg_match('/[\x{0600}-\x{06FF}]/u', $invoice->note) ? 'rtl' : 'ltr' }}" style="text-align: {{ preg_match('/[\x{0600}-\x{06FF}]/u', $invoice->note) ? 'right' : 'left' }}; display:inline-block; width:100%;">
                {{ $invoice->note }}
              </div>
            </div>
          @endif
        </div>
      </td>
      <td style="width: 52%;">
        <div class="total-box">
          <div class="total-row">Service total <strong style="float:right;">AED {{ number_format((float) $invoice->service_price, 2) }}</strong></div>
          <div class="total-row">Paid amount <strong style="float:right;">AED {{ number_format((float) $invoice->paid_amount, 2) }}</strong></div>
          <div class="total-row">Payment method <strong style="float:right;">{{ $paymentMethod }}</strong></div>
          <div class="total-row last">Total <strong style="float:right;">AED {{ number_format((float) $invoice->service_price, 2) }}</strong></div>
        </div>
      </td>
    </tr>
  </table>

  @if (method_exists($invoice, 'relationLoaded') && $invoice->relationLoaded('employees') && $invoice->employees->isNotEmpty())
    <div class="panel" style="margin-top: 6px;">
      <div class="label">Staff who served the customer</div>
      <div>{{ $invoice->employees->map(fn ($employee) => $employee->job_title_en ?: $employee->job_title ?: ($employee->full_name ?: $employee->first_name))->filter()->implode(', ') }}</div>
    </div>
  @endif

  <table class="notice-grid">
    <tr>
      <td class="notice-box" style="width: 100%;">
        <div class="notice-title">Important Service Notice</div>
        Results of protein and chemical hair services may vary depending on hair type and health condition. The salon is not responsible for any allergic reactions, hair damage, or side effects related to the client's health condition, hair condition, or personal sensitivities.
      </td>
    </tr>
  </table>

  <div class="footer">
    @if ($invoiceQrImage)
      <div class="qr-section">
        <div class="qr-box">
          <img src="{{ $invoiceQrImage }}" alt="Invoice QR">
          <div class="qr-title">Invoice QR</div>
        </div>
      </div>
    @endif
  </div>
</body>
</html>
