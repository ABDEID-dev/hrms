<?php

namespace App\Livewire\HumanResource\Attendance;

use App\Exports\ExportFingerprints;
use App\Imports\ImportFingerprints;
use App\Livewire\Sections\Navbar\Navbar;
use App\Models\Employee;
use App\Models\Fingerprint;
use App\Models\Import;
use App\Notifications\DefaultNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Fingerprints extends Component
{
    use WithFileUploads, WithPagination;

    // Variables - Start //
    public $employees = [];

    public $selectedEmployee;

    public $selectedEmployeeId;

    public $dateRange;

    public $fromDate;

    public $toDate;

    public $isAbsence = false;

    public $isOneFingerprint = false;

    public $confirmedId;

    public $isEdit = false;

    public $fingerprint;

    public $date;

    public $checkIn;

    public $checkOut;

    public $file;
    // Variables - End //

    public function mount()
    {
        $this->employees = Employee::all();

        $this->selectedEmployeeId = Auth::user()->employee_id;
        $this->selectedEmployee = Employee::find($this->selectedEmployeeId);

        $currentDate = Carbon::now();
        $previousMonth = $currentDate->copy()->subMonth();
        $this->dateRange = $previousMonth->format('Y-n-1').' to '.$currentDate;
    }

    public function render()
    {
        $fingerprints = $this->applyFilter();

        return view('livewire.human-resource.attendance.fingerprints', [
            'fingerprints' => $fingerprints,
        ]);
    }

    public function applyFilter()
    {
        // Employee
        $this->selectedEmployee = Employee::find($this->selectedEmployeeId);

        // Date range
        if ($this->dateRange) {
            $dates = explode(' to ', $this->dateRange);

            $this->fromDate = $dates[0];
            $this->toDate = $dates[1];
        }

        // Return filtered fingerprints
        return Fingerprint::filteredFingerprints(
            $this->selectedEmployee->id,
            $this->fromDate,
            $this->toDate,
            $this->isAbsence,
            $this->isOneFingerprint,
            $this->selectedEmployee->weekly_holiday
        )->paginate(7);
    }

    public function submitFingerprint()
    {
        $this->isEdit ? $this->editFingerprint() : $this->addFingerprint();
    }

    #[Renderless]
    public function showNewFingerprintModal()
    {
        abort_unless($this->canCreateFingerprints(), 403);

        $this->reset('isEdit', 'date', 'checkIn', 'checkOut');
    }

    #[Renderless]
    public function showEditFingerprintModal(Fingerprint $fingerprint)
    {
        abort_unless($this->canEditFingerprints(), 403);

        $this->isEdit = true;

        $this->fingerprint = $fingerprint;

        $this->date = $fingerprint->date;
        $this->checkIn = $fingerprint->check_in;
        $this->checkOut = $fingerprint->check_out;
    }

    public function addFingerprint()
    {
        abort_unless($this->canCreateFingerprints(), 403);

        $this->validate([
            'date' => 'required',
            'checkIn' => 'required',
            'checkOut' => 'required',
        ]);

        Fingerprint::create([
            'employee_id' => $this->selectedEmployeeId,
            'date' => $this->date,
            'log' => $this->checkIn.' '.$this->checkOut,
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
        ]);

        $this->dispatch('closeCanvas', elementId: '#addRecordSidebar');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }

    public function editFingerprint()
    {
        abort_unless($this->canEditFingerprints(), 403);

        $this->validate([
            'date' => 'required',
            'checkIn' => 'required',
            'checkOut' => 'required',
        ]);

        $this->fingerprint->update([
            'date' => $this->date,
            'log' => $this->checkIn.' '.$this->checkOut,
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
        ]);

        $this->dispatch('closeCanvas', elementId: '#addRecordSidebar');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

        $this->reset('isEdit', 'date', 'checkIn', 'checkOut');
    }

    public function confirmDeleteFingerprint($id)
    {
        abort_unless($this->canDeleteFingerprints(), 403);

        $this->confirmedId = $id;
    }

    public function deleteFingerprint(Fingerprint $fingerprint)
    {
        abort_unless($this->canDeleteFingerprints(), 403);

        $fingerprint->delete();
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }

    public function importFromExcel()
    {
        abort_unless($this->canImportFingerprints(), 403);

        $this->validate(
            [
                'file' => 'required|mimes:xlsx',
            ],
            [
                'file.required' => 'Please select a file to upload',
                'file.mimes' => 'Excel files is accepted only',
            ]
        );

        try {
            $fileRecord = Import::create([
                'file_name' => $this->file->getClientOriginalName(),
                'file_size' => $this->file->getSize(),
                'file_ext' => $this->file->getClientOriginalExtension(),
                'file_type' => $this->file->getClientMimeType(),
                'status' => 'waiting',
            ]);

            $destinationPath = 'imports';
            $path = Storage::putFileAs($destinationPath, $this->file, $this->file->getClientOriginalName());

            $this->dispatch('activeProgressBar')->to(Navbar::class);

            $import_date = new ImportFingerprints(Auth::user()->id, $fileRecord->id);
            Excel::import($import_date, $path);

            Notification::send(
                Auth::user(),
                new DefaultNotification(Auth::user()->id, 'Successfully imported the fingerprint file')
            );
            $this->dispatch('refreshNotifications')->to(Navbar::class);

            session()->flash('info', __('Stay tuned! The file is doing a little dance as we speak.'));
        } catch (Exception $e) {
            session()->flash('error', __('Error occurred: ').$e->getMessage());
        }

        $this->dispatch('closeModal', elementId: '#importModal');
    }

    public function exportToExcel()
    {
        abort_unless($this->canExportFingerprints(), 403);

        $fingerprints = Fingerprint::filteredFingerprints(
            $this->selectedEmployee->id,
            $this->fromDate,
            $this->toDate,
            $this->isAbsence,
            $this->isOneFingerprint
        )->get();

        $fileName = 'Fingerprints - '.Carbon::now();

        return Excel::download(new ExportFingerprints($fingerprints), $fileName.'.xlsx');
    }

    public function canManageFingerprints(): bool
    {
        return Auth::user()?->can('manage attendance fingerprints') || Auth::user()?->can('manage attendance');
    }

    public function canCreateFingerprints(): bool
    {
        return $this->canManageFingerprints() || Auth::user()?->can('create attendance fingerprints');
    }

    public function canEditFingerprints(): bool
    {
        return $this->canManageFingerprints() || Auth::user()?->can('edit attendance fingerprints');
    }

    public function canDeleteFingerprints(): bool
    {
        return $this->canManageFingerprints() || Auth::user()?->can('delete attendance fingerprints');
    }

    public function canImportFingerprints(): bool
    {
        return $this->canManageFingerprints() || Auth::user()?->can('import attendance fingerprints');
    }

    public function canExportFingerprints(): bool
    {
        return $this->canManageFingerprints() || Auth::user()?->can('export attendance fingerprints');
    }
}
