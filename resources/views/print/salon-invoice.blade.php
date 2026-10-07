@php
  $invoiceItems = $invoice->items->isNotEmpty()
    ? $invoice->items
    : collect([(object) [
        'service_name' => $invoice->service?->name,
        'service_name_en' => $invoice->service?->english_name,
        'line_total' => $invoice->service_price,
      ]]);

  $branchName = $branchesEn[$invoice->branch] ?? ucfirst(str_replace('_', ' ', (string) $invoice->branch));
  $customerName = $invoice->customer?->english_name ?: $invoice->customer?->name ?: 'Guest Customer';
  $invoiceNote = (string) ($invoice->note ?? '');
  $invoiceNoteIsArabic = $invoiceNote !== '' && preg_match('/[\x{0600}-\x{06FF}]/u', $invoiceNote);
  $creator = $invoice->employee;
  $createdBy = $creator?->job_title_en ?: $creator?->full_name ?: ($invoice->created_by ?: 'System');
  $paymentMethod = $invoice->payment_method === 'cash' ? 'Cash' : 'Card / Machine';
  $legalCompanyName = 'HURGHADA TRADING L.L.C';
  $taxRegistrationNumber = '104833256100003';
  $logoPath = public_path('assets/img/logo/LOGOHIs.png');
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
          new \BaconQrCode\Renderer\RendererStyle\RendererStyle(108, 1),
          new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
      );

      $svgString = (new \BaconQrCode\Writer($renderer))->writeString($qrPayload);
      $invoiceQrImage = 'data:image/svg+xml;base64,' . base64_encode($svgString);
  }
@endphp
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $invoice->invoice_number }}</title>
  <style>
    @page {
      size: A5 portrait;
      margin: 4mm;
    }

    * {
      box-sizing: border-box;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    :root {
      --invoice-print-scale: 1;
    }

    body {
      margin: 0;
      padding: 18px 0;
      background: #e9edf5;
      color: #121826;
      font-family: "Segoe UI", Arial, sans-serif;
      font-size: 12px;
      line-height: 1.45;
    }

    .page-actions {
      width: 188mm;
      max-width: calc(100% - 24px);
      margin: 0 auto 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      color: #667085;
      font-size: 12px;
    }

    .action-btn {
      border: 0;
      border-radius: 8px;
      padding: 9px 14px;
      background: #244315;
      color: #fff;
      font-weight: 800;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
    }

    .action-btn.secondary {
      background: #5b4df2;
    }

    .invoice-page {
      width: 188mm;
      max-width: calc(100% - 24px);
      min-height: 268mm;
      margin: 0 auto;
      background: #fff;
      border: 1px solid #dde3ee;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 22px 70px rgba(15, 23, 42, 0.15);
    }

    .top-rule {
      height: 9px;
      background: linear-gradient(90deg, #244315 0%, #244315 42%, #c8a64b 42%, #c8a64b 58%, #5b4df2 58%, #5b4df2 100%);
    }

    .header {
      padding: 22px 26px 18px;
      border-bottom: 1px solid #e6ebf3;
      background:
        linear-gradient(135deg, rgba(36, 67, 21, 0.06), transparent 44%),
        linear-gradient(315deg, rgba(91, 77, 242, 0.08), transparent 36%),
        #ffffff;
    }

    .header-grid {
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      gap: 18px;
      align-items: center;
    }

    .brand-row {
      display: block;
    }

  .logo-center {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding-left: 25px;
  }

    .brand-logo {
      width: 90px;
      height: 90px;
      border: 1px solid #e5dec8;
      border-radius: 18px;
      padding: 9px;
      object-fit: contain;
      background: #fff;
      box-shadow: 0 10px 26px rgba(36, 67, 21, 0.12);
    }

    .company {
      margin: 0;
      color: #244315;
      font-size: 30px;
      font-weight: 900;
      letter-spacing: 0;
    }

    .company-subtitle {
      color: #7b8498;
      margin-top: 2px;
      font-weight: 700;
    }

    .legal-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 10px;
    }

    .legal-chip {
      display: inline-flex;
      align-items: center;
      border: 1px solid #e5dec8;
      border-radius: 999px;
      padding: 5px 9px;
      background: #fffaf0;
      color: #6b5314;
      font-size: 10px;
      font-weight: 900;
      letter-spacing: 0.02em;
    }

    .invoice-heading {
      text-align: right;
    }

    .invoice-heading h1 {
      margin: 0;
      color: #121826;
      font-size: 38px;
      line-height: 1;
      font-weight: 900;
    }

    .invoice-pill {
      display: inline-flex;
      margin-top: 12px;
      padding: 7px 11px;
      border-radius: 999px;
      background: #f4f6ff;
      color: #5b4df2;
      border: 1px solid #dfe2ff;
      font-weight: 900;
      font-size: 11px;
    }

    .invoice-date {
      margin-top: 10px;
      color: #7b8498;
      font-weight: 700;
    }

    .qr-card {
      display: inline-block;
      margin: 16px auto 0;
      padding: 14px 16px;
      border: 1px solid #dde3ee;
      border-radius: 18px;
      background: #fff;
      text-align: center;
    }

    .qr-section {
      text-align: center;
      margin-top: 10px;
      break-inside: avoid;
      page-break-inside: avoid;
    }

    .qr-code {
      width: 220px;
      height: 220px;
      display: grid;
      place-items: center;
      margin: 0 auto;
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
    }

    .qr-code img {
      width: 220px;
      height: 220px;
      display: block;
    }

    .qr-title {
      color: #121826;
      font-size: 11px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-top: 7px;
    }

    .qr-subtitle {
      color: #667085;
      font-size: 10px;
      margin-top: 2px;
      max-width: 120px;
    }

    .content {
      padding: 22px 26px 20px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 12px;
      margin-bottom: 20px;
    }

    .panel {
      border: 1px solid #dde3ee;
      border-radius: 12px;
      padding: 13px 14px;
      background: #fbfcff;
      min-height: 86px;
    }

    .label {
      color: #667085;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      font-weight: 900;
      margin-bottom: 7px;
    }

    .value {
      color: #121826;
      font-size: 15px;
      font-weight: 900;
    }

    .muted {
      color: #667085;
      margin-top: 4px;
    }

    .items-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin-bottom: 18px;
      border: 1px solid #dde3ee;
      border-radius: 12px;
      overflow: hidden;
    }

    .items-table th {
      padding: 12px 14px;
      background: #244315;
      color: #fff;
      text-align: left;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .items-table td {
      padding: 13px 14px;
      border-bottom: 1px solid #edf1f7;
      vertical-align: top;
    }

    .items-table tr:last-child td {
      border-bottom: 0;
    }

    .service-name {
      color: #121826;
      font-weight: 900;
    }

    .text-right {
      text-align: right;
    }

    .bottom-grid {
      display: grid;
      grid-template-columns: 1fr 82mm;
      gap: 14px;
      align-items: start;
    }

    .care-box {
      border: 1px dashed #c8a64b;
      background: #fffaf0;
      color: #6b5314;
      border-radius: 12px;
      padding: 14px;
      min-height: 116px;
    }

    .totals {
      position: relative;
      border: 1px solid #dde3ee;
      border-radius: 12px;
      overflow: visible;
      background: #fff;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      gap: 14px;
      padding: 11px 13px;
      border-bottom: 1px solid #edf1f7;
      color: #344054;
    }

    .total-row strong {
      color: #121826;
    }

    .total-row.final {
      border-bottom: 0;
      background: #f4f6ff;
      color: #5b4df2;
      font-size: 16px;
      font-weight: 900;
    }

    .total-row.final strong {
      color: #5b4df2;
    }

    .staff-box {
      margin-top: 14px;
      border: 1px solid #dde3ee;
      background: #fbfcff;
      border-radius: 12px;
      padding: 13px 14px;
    }

    .footer {
      display: block;
      text-align: center;
      margin-top: 20px;
      padding-top: 16px;
      border-top: 1px solid #dde3ee;
    }

    .thanks {
      color: #121826;
      font-size: 14px;
      font-weight: 900;
    }

    .group {
      color: #667085;
      margin-top: 2px;
    }

    .stamp {
      color: #d8dde9;
      font-size: 30px;
      font-weight: 900;
      letter-spacing: 0.12em;
      margin-top: 10px;
    }

    .notice-grid {
      display: grid;
      grid-template-columns: 0.9fr 1.4fr;
      gap: 14px;
      margin-top: 16px;
    }

    .notice-grid.single-notice {
      grid-template-columns: 1fr;
    }

    .notice-box {
      border: 1px solid #e5dec8;
      border-radius: 12px;
      background: #fffaf0;
      padding: 14px;
      color: #5f4c18;
    }

    .notice-title {
      color: #8b6b17;
      font-size: 10px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 7px;
    }

    .notice-phone {
      color: #244315;
      font-size: 17px;
      font-weight: 900;
      margin-top: 7px;
    }

    .invoice-note {
      display: block;
      margin-top: 8px;
      white-space: pre-wrap;
      unicode-bidi: plaintext;
      word-break: break-word;
    }

    .rtl-text {
      direction: rtl;
      text-align: right;
      font-family: Tahoma, "Segoe UI", Arial, sans-serif;
    }

    .ltr-text {
      direction: ltr;
      text-align: left;
    }

    @media screen {
      .invoice-page {
        transform-origin: top center;
      }
    }

    @media print {
      html,
      body {
        width: 140mm;
        height: 202mm;
        margin: 0;
        padding: 0;
        background: #fff;
        overflow: hidden;
        position: relative;
      }

      .no-print {
        display: none !important;
      }

      .invoice-page {
        position: absolute;
        top: 0;
        left: 50%;
        width: 140mm;
        max-width: 140mm;
        min-height: auto;
        height: auto;
        margin: 0;
        border-radius: 0;
        border: 0;
        box-shadow: none;
        transform: translateX(-50%) scale(var(--invoice-print-scale));
        transform-origin: top center;
        page-break-after: avoid;
        page-break-before: avoid;
        page-break-inside: avoid;
        break-after: avoid;
        break-before: avoid;
        break-inside: avoid;
      }

      .top-rule {
        height: 2mm;
      }

      .header {
        padding: 4mm 4.5mm 3mm;
      }

      .header-grid {
        grid-template-columns: 1fr auto 1fr;
        gap: 3mm;
      }

      .company {
        font-size: 16pt;
        line-height: 1.05;
      }

      .company-subtitle {
        font-size: 6.2pt;
        margin-top: .7mm;
      }

      .legal-meta {
        gap: 1.5mm;
        margin-top: 2mm;
      }

      .legal-chip {
        padding: 1.1mm 2mm;
        font-size: 5.4pt;
      }

      .logo-center {
        padding-left: 0;
      }

      .brand-logo {
        width: 20mm;
        height: 20mm;
        border-radius: 3mm;
        padding: 1.5mm;
      }

      .invoice-heading h1 {
        font-size: 20pt;
      }

      .invoice-pill {
        margin-top: 2mm;
        padding: 1.4mm 2.2mm;
        font-size: 5.8pt;
      }

      .invoice-date {
        margin-top: 1.6mm;
        font-size: 6pt;
      }

      .content {
        padding: 3.5mm 4.5mm 3mm;
      }

      .info-grid {
        gap: 2mm;
        margin-bottom: 3mm;
      }

      .panel {
        min-height: 0;
        padding: 2.4mm 2.6mm;
        border-radius: 2mm;
      }

      .label {
        font-size: 5.4pt;
        margin-bottom: 1.2mm;
      }

      .value {
        font-size: 8pt;
      }

      .muted {
        margin-top: 1mm;
        font-size: 6pt;
      }

      .items-table {
        margin-bottom: 3mm;
        border-radius: 2mm;
      }

      .items-table th {
        padding: 1.8mm 2mm;
        font-size: 5.5pt;
      }

      .items-table td {
        padding: 1.9mm 2mm;
        font-size: 6.2pt;
      }

      .bottom-grid {
        grid-template-columns: minmax(0, 1fr) 54mm;
        gap: 2.5mm;
      }

      .care-box,
      .totals,
      .staff-box,
      .notice-box {
        border-radius: 2mm;
      }

      .care-box {
        min-height: 0;
        padding: 2.4mm;
        font-size: 6pt;
      }

      .total-row {
        padding: 1.8mm 2mm;
        font-size: 6.3pt;
      }

      .total-row.final {
        font-size: 8pt;
      }

      .staff-box {
        margin-top: 2.5mm;
        padding: 2.4mm 2.6mm;
        font-size: 6.3pt;
      }

      .notice-grid {
        gap: 2.5mm;
        margin-top: 3mm;
      }

      .notice-box {
        padding: 2.4mm;
        font-size: 5.8pt;
        line-height: 1.35;
      }

      .notice-title {
        font-size: 5.4pt;
        margin-bottom: 1.2mm;
      }

      .notice-phone {
        font-size: 8pt;
        margin-top: 1.2mm;
      }

      .footer {
        margin-top: 3mm;
        padding-top: 2.5mm;
      }

      .thanks {
        font-size: 7pt;
      }

      .group {
        font-size: 6pt;
      }

      .qr-section {
        margin-top: 2.5mm;
      }

      .qr-card {
        padding: 2.5mm;
        border-radius: 2mm;
      }

      .qr-code,
      .qr-code img {
        width: 28mm;
        height: 28mm;
      }

      .qr-title {
        font-size: 5.8pt;
        margin-top: 1.2mm;
      }

      .qr-subtitle,
      .stamp {
        display: none;
      }

    }
  </style>
</head>
<body>
  <div class="page-actions no-print">
    <div>
      @if (!empty($pdfUnavailableMessage))
        {{ $pdfUnavailableMessage }}
      @else
        سيتم فتح الطباعة مباشرة بمقاس A5. لو ظهر رابط المتصفح في الورقة، أغلق خيار Headers and footers من نافذة الطباعة.
      @endif
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <button type="button" class="action-btn" onclick="window.print()">طباعة الآن</button>
    </div>
  </div>

  <main class="invoice-page">
    <div class="top-rule"></div>

    <header class="header">
      <div class="header-grid">
        <div class="brand-row">
          <div>
            <h2 class="company">Hurghada Salon</h2>
            <div class="company-subtitle">Professional Beauty Services</div>
            <div class="company-subtitle">Branch: {{ $branchName }}</div>
            <div class="legal-meta">
              <span class="legal-chip">{{ $legalCompanyName }}</span>
              <span class="legal-chip">TRN {{ $taxRegistrationNumber }}</span>
            </div>
          </div>
        </div>

        <div class="logo-center">
          @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="Hurghada Salon" class="brand-logo">
          @else
            <div class="brand-logo" style="display:grid; place-items:center; font-weight:bold; font-size:24px;">H</div>
          @endif
        </div>

        <div class="invoice-heading">
          <h1>Invoice</h1>
          <div class="invoice-pill">{{ $invoice->invoice_number }}</div>
          <div class="invoice-date">{{ $invoice->invoice_date?->format('d M Y - h:i A') }}</div>
        </div>
      </div>
    </header>

    <section class="content">
      <div class="info-grid">
        <div class="panel">
          <div class="label">Customer</div>
          <div class="value">{{ $customerName }}</div>
          <div class="muted">{{ $invoice->customer?->phone ?: 'No phone number' }}</div>
        </div>

        <div class="panel">
          <div class="label">Invoice Details</div>
          <div class="value">{{ $invoiceItems->count() }} service{{ $invoiceItems->count() === 1 ? '' : 's' }}</div>
          <div class="muted">Created by: {{ $createdBy }}</div>
        </div>

        <div class="panel">
          <div class="label">Payment</div>
          <div class="value">{{ $paymentMethod }}</div>
          <div class="muted">Paid amount: AED {{ number_format((float) $invoice->paid_amount, 2) }}</div>
        </div>
      </div>

      <table class="items-table">
        <thead>
          <tr>
            <th style="width: 54%;">Service</th>
            <th>Branch</th>
            <th class="text-right">Price</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($invoiceItems as $item)
            <tr>
              <td><div class="service-name">{{ $item->service_name_en ?: $item->service_name }}</div></td>
              <td>{{ $branchName }}</td>
              <td class="text-right">AED {{ number_format((float) $item->line_total, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="bottom-grid">
        <div class="care-box">
          <div class="label">Customer Care</div>
          If you have any complaint or note about our services, please contact us directly. We will be happy to assist you.
          @if (filled($invoiceNote))
            <div class="invoice-note {{ $invoiceNoteIsArabic ? 'rtl-text' : 'ltr-text' }}" dir="{{ $invoiceNoteIsArabic ? 'rtl' : 'ltr' }}">
              <strong>{{ $invoiceNoteIsArabic ? 'ملاحظة:' : 'Note:' }}</strong> {{ $invoiceNote }}
            </div>
          @endif
        </div>

        <div class="totals">
          <div class="total-row">
            <span>Service total</span>
            <strong>AED {{ number_format((float) $invoice->service_price, 2) }}</strong>
          </div>
          <div class="total-row">
            <span>Paid amount</span>
            <strong>AED {{ number_format((float) $invoice->paid_amount, 2) }}</strong>
          </div>
          <div class="total-row">
            <span>Payment method</span>
            <strong>{{ $paymentMethod }}</strong>
          </div>
          <div class="total-row final">
            <span>Total</span>
            <strong>AED {{ number_format((float) $invoice->service_price, 2) }}</strong>
          </div>
        </div>
      </div>

      @if (method_exists($invoice, 'relationLoaded') && $invoice->relationLoaded('employees') && $invoice->employees->isNotEmpty())
        <div class="staff-box">
          <div class="label">Staff who served the customer</div>
          <div>{{ $invoice->employees->map(fn ($employee) => $employee->job_title_en ?: $employee->job_title ?: ($employee->full_name ?: $employee->first_name))->filter()->implode(', ') }}</div>
        </div>
      @endif

      <div class="notice-grid single-notice">
        <div class="notice-box">
          <div class="notice-title">Important Service Notice</div>
          Results of protein and chemical hair services may vary depending on hair type and health condition. The salon is not responsible for any allergic reactions, hair damage, or side effects related to the client's health condition, hair condition, or personal sensitivities.
        </div>
      </div>

      <footer class="footer">
      @if ($invoiceQrImage)
          <div class="qr-section">
            <div class="qr-card">
            <div class="qr-code"><img src="{{ $invoiceQrImage }}" alt="QR Code"></div>
              <div class="qr-title">Invoice QR</div>
              <div class="qr-subtitle">Scan to read invoice details</div>
            </div>
          </div>
        @endif
        <div class="stamp">HURGHADA</div>
      </footer>
    </section>
  </main>

  <script>
    function fitInvoiceToSingleA5Page() {
      const invoicePage = document.querySelector('.invoice-page');

      if (!invoicePage) {
        return;
      }

      document.documentElement.style.setProperty('--invoice-print-scale', '1');

      const mmProbe = document.createElement('div');
      mmProbe.style.position = 'absolute';
      mmProbe.style.visibility = 'hidden';
      mmProbe.style.pointerEvents = 'none';
      mmProbe.style.width = '140mm';
      mmProbe.style.height = '202mm';
      document.body.appendChild(mmProbe);

      const printableWidth = mmProbe.offsetWidth;
      const printableHeight = mmProbe.offsetHeight;
      mmProbe.remove();

      const scale = Math.min(
        1,
        printableWidth / Math.max(invoicePage.scrollWidth, 1),
        printableHeight / Math.max(invoicePage.scrollHeight, 1)
      );

      document.documentElement.style.setProperty('--invoice-print-scale', scale.toString());
    }

    window.addEventListener('beforeprint', fitInvoiceToSingleA5Page);

    @if (!empty($autoPrint))
      window.addEventListener('load', function () {
        setTimeout(function () {
          fitInvoiceToSingleA5Page();
          window.print();
        }, 350);
      });
    @endif
  </script>
</body>
</html>
