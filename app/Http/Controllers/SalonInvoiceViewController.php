<?php

namespace App\Http\Controllers;

use App\Models\SalonInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class SalonInvoiceViewController extends Controller
{
    public function __invoke(Request $request, SalonInvoice $invoice)
    {
        abort_unless($request->hasValidSignature(), 403);

        $relations = ['customer', 'service', 'employee', 'items'];

        if (Schema::hasTable('salon_invoice_employee')) {
            $relations[] = 'employees';
        }

        $invoice->loadMissing($relations);

        return view('print.salon-invoice', [
            'invoice' => $invoice,
            'autoPrint' => false,
            'locale' => 'en',
            'pdfUrl' => URL::temporarySignedRoute('salon-invoices-pdf', now()->addHours(12), [
                'invoice' => $invoice->id,
            ]),
            'brandNameAr' => 'هوركادا صالون',
            'brandNameEn' => 'Hurghada Salon',
            'branches' => [
                'avani' => 'أفاني',
                'night_cassia' => 'نايت كاسل',
            ],
            'branchesEn' => [
                'avani' => 'Avani',
                'night_cassia' => 'Knight Castle Hotel',
            ],
        ]);
    }
}
