<?php

use App\Http\Controllers\EmployeeComplaintFileController;
use App\Http\Controllers\EmployeeDocumentFileController;
use App\Http\Controllers\EmployeeLocationController;
use App\Http\Controllers\EmployeeProfilePhotoController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\SalonInvoicePdfController;
use App\Http\Controllers\SalonInvoicePrintController;
use App\Http\Controllers\SalonInvoiceViewController;
use App\Http\Controllers\SecretArchiveFileController;
use App\Livewire\Accounts\EmployeeRevenues;
use App\Livewire\Accounts\ExpenseReports;
use App\Livewire\Accounts\Expenses as AccountExpenses;
use App\Livewire\Accounts\MonthlyIncomeReport;
use App\Livewire\Accounts\PayrollPayments;
use App\Livewire\Accounts\Revenues as AccountRevenues;
use App\Livewire\Accounts\Treasury as AccountTreasury;
use App\Livewire\Accounts\TreasuryAudit;
use App\Livewire\Assets\Categories;
use App\Livewire\Assets\Inventory;
use App\Livewire\Assets\MaktoomDyeReports;
use App\Livewire\Assets\MaktoomDyeRevenues;
use App\Livewire\Assets\MaktoomDyes;
use App\Livewire\Assets\Reports as InventoryReports;
use App\Livewire\ContactUs;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Dashboard;
use App\Livewire\Employee\Complaints;
use App\Livewire\Employee\ManagementResponses;
use App\Livewire\Employee\Portal as EmployeePortal;
use App\Livewire\HumanResource\Attendance\AdminTracking;
use App\Livewire\HumanResource\Attendance\Fingerprints;
use App\Livewire\HumanResource\Attendance\Leaves;
use App\Livewire\HumanResource\Attendance\LocationReport;
use App\Livewire\HumanResource\Discounts;
use App\Livewire\HumanResource\Holidays;
use App\Livewire\HumanResource\Messages\Bulk;
use App\Livewire\HumanResource\Messages\Complaints as ManagementComplaints;
use App\Livewire\HumanResource\Messages\DeletedDocuments;
use App\Livewire\HumanResource\Messages\EmployeeRequests;
use App\Livewire\HumanResource\Messages\Personal;
use App\Livewire\HumanResource\SalaryReport;
use App\Livewire\HumanResource\Statistics;
use App\Livewire\HumanResource\Structure\Centers;
use App\Livewire\HumanResource\Structure\Departments;
use App\Livewire\HumanResource\Structure\EmployeeDocuments;
use App\Livewire\HumanResource\Structure\EmployeeInfo;
use App\Livewire\HumanResource\Structure\Employees;
use App\Livewire\HumanResource\Structure\Positions;
use App\Livewire\MaintenanceMode;
use App\Livewire\Salon\Invoices as SalonInvoices;
use App\Livewire\Salon\Services as SalonServices;
use App\Livewire\SecretArchive\Index as SecretArchiveIndex;
use App\Livewire\Settings\Permissions as SettingsPermissions;
use App\Livewire\Settings\Roles as SettingsRoles;
use App\Livewire\Settings\Users;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// Fallback serving of the "public" storage disk for hosts where the
// `public/storage` symlink cannot be created (e.g. shared hosting).
// When the symlink exists, Apache serves the file directly and this route is not reached.
Route::get('/storage/{path}', function (string $path) {
    if (str_contains($path, '..')) {
        abort(404);
    }

    // Only serve folders that are meant to be public. Protected folders
    // (employee documents & complaint attachments) stay behind their
    // authenticated routes and must never be exposed here.
    abort_unless(
        Str::startsWith($path, ['inventory-products/', 'account-invoices/', 'profile-photos/']),
        404
    );

    $disk = Storage::disk('public');

    abort_unless($disk->exists($path), 404);

    return $disk->response($path);
})->where('path', '.*')->name('storage.public-files');

Route::get('/salon/invoices/{invoice}/pdf', SalonInvoicePdfController::class)
    ->middleware('signed')
    ->name('salon-invoices-pdf');
Route::get('/salon/invoices/{invoice}/print', SalonInvoicePrintController::class)
    ->middleware('signed')
    ->name('salon-invoices-print');
Route::get('/salon/invoices/{invoice}/view', SalonInvoiceViewController::class)
    ->middleware('signed')
    ->name('salon-invoices-view');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'allow_admin_during_maintenance',
])->group(function () {
    Route::redirect('/', '/dashboard')->middleware('permission:view dashboard');
    Route::get('/dashboard', Dashboard::class)
        ->middleware('permission:view dashboard')
        ->name('dashboard');

    Route::get('/employee/portal', EmployeePortal::class)
        ->middleware('permission:view employee portal')
        ->name('employee-portal');
    Route::get('/employee/complaints', Complaints::class)
        ->middleware('permission:view employee complaints')
        ->name('employee-complaints');
    Route::get('/employee/management-responses', ManagementResponses::class)
        ->middleware('permission:view employee management responses')
        ->name('employee-management-responses');
    Route::get('/employee/profile-photo/{employee}', EmployeeProfilePhotoController::class)
        ->name('employee-profile-photo');
    Route::get('/employee/documents/{document}/file', EmployeeDocumentFileController::class)
        ->name('employee-documents-file');
    Route::get('/employee/complaint-files/{path}', EmployeeComplaintFileController::class)
        ->where('path', '.*')
        ->name('employee-complaints-file');
    Route::get('/secret-archive', SecretArchiveIndex::class)
        ->name('secret-archive');
    Route::get('/secret-archive/files/{file}/view', [SecretArchiveFileController::class, 'view'])
        ->name('secret-archive-files.view');
    Route::get('/secret-archive/files/{file}/download', [SecretArchiveFileController::class, 'download'])
        ->name('secret-archive-files.download');

    Route::prefix('attendance')->group(function () {
        Route::get('/fingerprints', Fingerprints::class)
            ->middleware('permission:view attendance fingerprints')
            ->name('attendance-fingerprints');
        Route::get('/leaves', Leaves::class)
            ->middleware('permission:view attendance leaves')
            ->name('attendance-leaves');
    });

    Route::post('/employee-location/system-open', [EmployeeLocationController::class, 'systemOpen'])
        ->name('employee-location-system-open');
    Route::get('/employee-tracking', LocationReport::class)
        ->name('employee-tracking');
    Route::get('/admin-tracking', AdminTracking::class)
        ->name('admin-tracking');

    Route::prefix('structure')->group(function () {
        Route::get('/centers', Centers::class)
            ->middleware('permission:view structure centers')
            ->name('structure-centers');
        Route::get('/departments', Departments::class)
            ->middleware('permission:view structure departments')
            ->name('structure-departments');
        Route::get('/positions', Positions::class)
            ->middleware('permission:view structure positions')
            ->name('structure-positions');
        Route::get('/employees', Employees::class)
            ->middleware('permission:view employees')
            ->name('structure-employees');
        Route::get('/employee-documents', EmployeeDocuments::class)
            ->middleware('permission:view employee documents')
            ->name('structure-employee-documents');
    });

    Route::get('/structure/employee/{id?}', EmployeeInfo::class)
        ->middleware('permission:view employee details')
        ->name('structure-employees-info');

    Route::prefix('messages')->group(function () {
        Route::get('/bulk', Bulk::class)
            ->middleware('permission:view messages bulk')
            ->name('messages-bulk');
        Route::get('/personal', Personal::class)
            ->middleware('permission:view messages personal')
            ->name('messages-personal');
        Route::get('/employee-requests', EmployeeRequests::class)
            ->middleware('permission:view employee requests')
            ->name('messages-employee-requests');
        Route::get('/complaints', ManagementComplaints::class)
            ->middleware('permission:manage management complaints')
            ->name('management-complaints');
        Route::get('/deleted-documents', DeletedDocuments::class)
            ->middleware('permission:view deleted documents')
            ->name('messages-deleted-documents');
    });

    Route::get('/discounts', Discounts::class)
        ->middleware('permission:view discounts')
        ->name('discounts');
    Route::get('/salary-report', SalaryReport::class)
        ->middleware('role:Admin')
        ->name('salary-report');
    Route::get('/customers', CustomersIndex::class)
        ->middleware('permission:view customers')
        ->name('customers');

    Route::prefix('accounts')->group(function () {
        Route::get('/employee-revenues', EmployeeRevenues::class)
            ->middleware('permission:view employee revenues')
            ->name('accounts-employee-revenues');
        Route::get('/treasury-audit', TreasuryAudit::class)
            ->middleware('role:Admin')
            ->name('accounts-treasury-audit');
        Route::get('/expense-reports', ExpenseReports::class)
            ->middleware('permission:view accounts treasury')
            ->name('accounts-expense-reports');
        Route::get('/payroll-payments', PayrollPayments::class)
            ->middleware('role:Admin|ManagementEmployee')
            ->name('accounts-payroll-payments');

        Route::get('/maktoom/revenues', AccountRevenues::class)
            ->middleware(['permission:view accounts maktoom', 'permission:manage accounts revenues|create account revenues|edit account revenues|delete account revenues|create backdated account revenues|edit backdated account revenues|delete backdated account revenues', 'account.access:maktoom'])
            ->defaults('account', 'maktoom')
            ->name('accounts-maktoom-revenues');
        Route::get('/maktoom/expenses', AccountExpenses::class)
            ->middleware(['permission:view accounts maktoom', 'permission:manage accounts expenses|create account expenses|create backdated account expenses|edit account expenses|delete account expenses|edit backdated account expenses|delete backdated account expenses', 'account.access:maktoom'])
            ->defaults('account', 'maktoom')
            ->name('accounts-maktoom-expenses');
        Route::get('/maktoom/treasury', AccountTreasury::class)
            ->middleware(['permission:view accounts maktoom', 'permission:view accounts treasury', 'account.access:maktoom'])
            ->defaults('account', 'maktoom')
            ->name('accounts-maktoom-treasury');
        Route::get('/maktoom/monthly-income-report', MonthlyIncomeReport::class)
            ->middleware(['permission:view accounts maktoom', 'permission:view accounts treasury', 'account.access:maktoom'])
            ->defaults('account', 'maktoom')
            ->name('accounts-maktoom-monthly-income-report');

        Route::get('/avani/revenues', AccountRevenues::class)
            ->middleware(['permission:view accounts avani', 'permission:manage accounts revenues|create account revenues|edit account revenues|delete account revenues|create backdated account revenues|edit backdated account revenues|delete backdated account revenues', 'account.access:avani'])
            ->defaults('account', 'avani')
            ->name('accounts-avani-revenues');
        Route::get('/avani/expenses', AccountExpenses::class)
            ->middleware(['permission:view accounts avani', 'permission:manage accounts expenses|create account expenses|create backdated account expenses|edit account expenses|delete account expenses|edit backdated account expenses|delete backdated account expenses', 'account.access:avani'])
            ->defaults('account', 'avani')
            ->name('accounts-avani-expenses');
        Route::get('/avani/treasury', AccountTreasury::class)
            ->middleware(['permission:view accounts avani', 'permission:view accounts treasury', 'account.access:avani'])
            ->defaults('account', 'avani')
            ->name('accounts-avani-treasury');
        Route::get('/avani/monthly-income-report', MonthlyIncomeReport::class)
            ->middleware(['permission:view accounts avani', 'permission:view accounts treasury', 'account.access:avani'])
            ->defaults('account', 'avani')
            ->name('accounts-avani-monthly-income-report');

        Route::get('/perfumes/revenues', AccountRevenues::class)
            ->middleware(['permission:view accounts perfumes', 'permission:manage accounts revenues|create account revenues|edit account revenues|delete account revenues|create backdated account revenues|edit backdated account revenues|delete backdated account revenues', 'account.access:perfumes'])
            ->defaults('account', 'perfumes')
            ->name('accounts-perfumes-revenues');
        Route::get('/perfumes/expenses', AccountExpenses::class)
            ->middleware(['permission:view accounts perfumes', 'permission:manage accounts expenses|create account expenses|create backdated account expenses|edit account expenses|delete account expenses|edit backdated account expenses|delete backdated account expenses', 'account.access:perfumes'])
            ->defaults('account', 'perfumes')
            ->name('accounts-perfumes-expenses');
        Route::get('/perfumes/treasury', AccountTreasury::class)
            ->middleware(['permission:view accounts perfumes', 'permission:view accounts treasury', 'account.access:perfumes'])
            ->defaults('account', 'perfumes')
            ->name('accounts-perfumes-treasury');
        Route::get('/perfumes/monthly-income-report', MonthlyIncomeReport::class)
            ->middleware(['permission:view accounts perfumes', 'permission:view accounts treasury', 'account.access:perfumes'])
            ->defaults('account', 'perfumes')
            ->name('accounts-perfumes-monthly-income-report');
    });

    Route::get('/holidays', Holidays::class)
        ->middleware('permission:view holidays')
        ->name('holidays');
    Route::get('/statistics', Statistics::class)
        ->middleware('permission:view statistics')
        ->name('statistics');

    Route::prefix('settings')->group(function () {
        Route::get('/users', Users::class)
            ->middleware('permission:view settings users')
            ->name('settings-users');
        Route::get('/roles', SettingsRoles::class)
            ->middleware(['permission:view settings roles', 'permission:manage permissions'])
            ->name('settings-roles');
        Route::get('/permissions', SettingsPermissions::class)
            ->middleware(['permission:view settings permissions', 'permission:manage permissions'])
            ->name('settings-permissions');
    });
    Route::get('/assets/inventory', Inventory::class)
        ->middleware('permission:view assets inventory|view accounts maktoom|view accounts avani|view accounts perfumes')
        ->name('inventory');
    Route::get('/maktoom/dyes', MaktoomDyes::class)
        ->middleware(['permission:view maktoom dyes', 'account.access:maktoom'])
        ->name('maktoom-dyes');
    Route::get('/maktoom/dye-revenues', MaktoomDyeRevenues::class)
        ->middleware(['permission:view maktoom dyes', 'account.access:maktoom'])
        ->name('maktoom-dye-revenues');
    Route::get('/maktoom/dye-reports', MaktoomDyeReports::class)
        ->middleware(['permission:view maktoom dyes', 'account.access:maktoom'])
        ->name('maktoom-dye-reports');
    Route::get('/assets/categories', Categories::class)
        ->middleware('permission:view assets categories')
        ->name('categories');
    Route::get('/assets/reports', InventoryReports::class)
        ->middleware('permission:view assets reports')
        ->name('reports');

    Route::get('/salon/services', SalonServices::class)
        ->middleware('permission:view salon services')
        ->name('salon-services');
    Route::get('/salon/invoices', SalonInvoices::class)
        ->middleware('permission:view salon invoices')
        ->name('salon-invoices');

    Route::get('/contact-us', ContactUs::class)->name('contact-us');
    Route::get('/maintenance-mode', MaintenanceMode::class)->name('maintenance-mode');
});

Route::webhooks('/deploy');
