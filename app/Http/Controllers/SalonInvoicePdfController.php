<?php

namespace App\Http\Controllers;

use App\Models\SalonInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SalonInvoicePdfController extends Controller
{
    public function __invoke(Request $request, SalonInvoice $invoice)
    {
        abort_unless($request->hasValidSignature(), 403);

        $relations = ['customer', 'service', 'employee', 'items'];

        if (Schema::hasTable('salon_invoice_employee')) {
            $relations[] = 'employees';
        }

        $invoice->loadMissing($relations);

        if (! extension_loaded('gd')) {
            return view('print.salon-invoice', [
                'invoice' => $invoice,
                'autoPrint' => false,
                'locale' => 'en',
                'pdfUnavailableMessage' => 'PDF generation needs the PHP GD extension. It is installed on the server, but missing locally.',
                'brandNameAr' => 'هورغادا صالون',
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

        $pdf = Pdf::loadView('pdf.salon-invoice', [
            'invoice' => $invoice,
            'branches' => [
                'avani' => 'Avani',
                'night_cassia' => 'Knight Castle Hotel',
            ],
        ])->setPaper('a5', 'portrait');

        return $pdf->stream(($invoice->invoice_number ?: 'invoice').'.pdf');
    }
}
