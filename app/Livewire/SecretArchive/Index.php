<?php

namespace App\Livewire\SecretArchive;

use App\Models\SecretArchiveFolder;
use App\Support\SecretArchivePermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public string $account = 'maktoom';

    public string $folderName = '';

    public string $folderPin = '';

    public string $unlockPin = '';

    public string $newFolderPin = '';

    public array $archiveFiles = [];

    public ?int $activeFolderId = null;

    public ?int $revealedPinFolderId = null;

    public ?string $revealedFolderPin = null;

    public ?int $confirmedDeleteFolderId = null;

    public ?int $confirmedDeleteFileId = null;

    private array $accountLabels = [
        'maktoom' => 'ui.secret_archive_maktoom',
        'avani' => 'ui.secret_archive_avani',
    ];

    public function mount(): void
    {
        $accounts = SecretArchivePermissions::accessibleAccounts(Auth::user());
        abort_if($accounts === [], 403);

        $requestedAccount = (string) request()->query('account', '');
        $this->account = in_array($requestedAccount, $accounts, true)
            ? $requestedAccount
            : $accounts[0];
    }

    public function updatingAccount(string $account): void
    {
        abort_unless(SecretArchivePermissions::canView(Auth::user(), $account), 403);
        $this->closeActiveFolder();
    }

    public function render()
    {
        $accounts = SecretArchivePermissions::accessibleAccounts(Auth::user());
        $folders = SecretArchiveFolder::query()
            ->where('account', $this->account)
            ->select(['id', 'account', 'name', 'password_version', 'created_by', 'created_at', 'updated_at'])
            ->withCount('files')
            ->latest('id')
            ->get()
            ->each(function (SecretArchiveFolder $folder): void {
                $folder->setAttribute('is_unlocked', $this->folderIsUnlocked($folder));
            });

        $activeFolder = $folders->firstWhere('id', $this->activeFolderId);
        $activeFiles = $activeFolder && $this->folderIsUnlocked($activeFolder)
            ? $activeFolder->files()
                ->select(['id', 'folder_id', 'original_name', 'mime', 'size', 'uploaded_by', 'created_at'])
                ->latest()
                ->get()
            : collect();

        return view('livewire.secret-archive.index', [
            'accounts' => $accounts,
            'accountLabels' => $this->accountLabels,
            'folders' => $folders,
            'activeFolder' => $activeFolder,
            'activeFiles' => $activeFiles,
            'canManageCurrentAccount' => SecretArchivePermissions::canManage(Auth::user(), $this->account),
            'isSuperAdmin' => $this->isSuperAdmin(),
        ]);
    }

    public function createFolder(): void
    {
        abort_unless(SecretArchivePermissions::canManage(Auth::user(), $this->account), 403);

        $this->validate([
            'account' => ['required', Rule::in(SecretArchivePermissions::accessibleAccounts(Auth::user()))],
            'folderName' => [
                'required',
                'string',
                'max:120',
                Rule::unique('secret_archive_folders', 'name')->where(fn ($query) => $query->where('account', $this->account)),
            ],
            'folderPin' => ['required', 'string', 'regex:/^\d{4,12}$/'],
        ], [
            'folderPin.regex' => __('ui.secret_archive_pin'),
        ]);

        $folder = SecretArchiveFolder::query()->create([
            'account' => $this->account,
            'name' => trim($this->folderName),
            'secret_encrypted' => Crypt::encryptString($this->folderPin),
            'password_version' => 1,
            'created_by' => Auth::id(),
        ]);

        $this->closeActiveFolder();
        session()->put($this->unlockSessionKey($folder), $folder->password_version);
        $this->activeFolderId = $folder->id;
        $this->reset('folderName', 'folderPin');
        $this->dispatch('toastr', type: 'success', message: __('ui.secret_archive_folder_created'));
    }

    public function openFolder(int $folderId): void
    {
        $folder = $this->folderForCurrentAccount($folderId);

        if ($this->activeFolderId && $this->activeFolderId !== $folder->id) {
            $this->closeActiveFolder();
        }

        $this->activeFolderId = $folder->id;
        $this->unlockPin = '';
        $this->resetValidation('unlockPin');

        if ($this->isSuperAdmin()) {
            session()->put($this->unlockSessionKey($folder), $folder->password_version);
        }
    }

    public function unlockFolder(): void
    {
        $this->validate([
            'activeFolderId' => ['required', 'integer', 'exists:secret_archive_folders,id'],
            'unlockPin' => ['required', 'string', 'regex:/^\d{4,12}$/'],
        ]);

        $folder = $this->folderForCurrentAccount((int) $this->activeFolderId);

        if ($this->isSuperAdmin()) {
            session()->put($this->unlockSessionKey($folder), $folder->password_version);
            $this->reset('unlockPin');

            return;
        }

        $attemptKey = 'secret-archive-pin:'.Auth::id().':'.$folder->id;

        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            $this->addError('unlockPin', __('ui.secret_archive_pin_throttled'));

            return;
        }

        try {
            $matches = hash_equals(Crypt::decryptString($folder->secret_encrypted), $this->unlockPin);
        } catch (\Throwable) {
            $matches = false;
        }

        if (! $matches) {
            RateLimiter::hit($attemptKey, 60);
            $this->addError('unlockPin', __('ui.secret_archive_wrong_pin'));

            return;
        }

        RateLimiter::clear($attemptKey);
        session()->put($this->unlockSessionKey($folder), $folder->password_version);
        $this->reset('unlockPin');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function lockFolder(int $folderId): void
    {
        $folder = $this->folderForCurrentAccount($folderId);
        session()->forget($this->unlockSessionKey($folder));

        if ($this->activeFolderId === $folder->id) {
            $this->closeActiveFolder();
        }
    }

    public function uploadFiles(): void
    {
        abort_if($this->archiveFiles === [], 422);
        $folder = $this->activeFolderForManagement();

        $this->validate([
            'archiveFiles' => ['required', 'array', 'min:1', 'max:10'],
            'archiveFiles.*' => [
                'required',
                'file',
                'max:51200',
                'mimes:jpg,jpeg,png,gif,bmp,webp,mp4,mov,avi,mkv,webm,pdf,doc,docx,xls,xlsx,txt',
            ],
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use ($folder, &$storedPaths): void {
                foreach ($this->archiveFiles as $archiveFile) {
                    $path = $archiveFile->store('secret-archive/'.$folder->id, 'local');
                    $storedPaths[] = $path;

                    $folder->files()->create([
                        'path' => $path,
                        'original_name' => mb_substr(basename(str_replace('\\', '/', $archiveFile->getClientOriginalName())), 0, 255),
                        'mime' => $archiveFile->getMimeType() ?: 'application/octet-stream',
                        'size' => (int) $archiveFile->getSize(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);

            throw $exception;
        }

        $this->reset('archiveFiles');
        $this->dispatch('toastr', type: 'success', message: __('ui.secret_archive_file_uploaded'));
    }

    public function removeFile(int $index): void
    {
        if (! array_key_exists($index, $this->archiveFiles)) {
            return;
        }

        $removedFile = $this->archiveFiles[$index];
        unset($this->archiveFiles[$index]);
        $this->archiveFiles = array_values($this->archiveFiles);

        if (method_exists($removedFile, 'delete')) {
            $removedFile->delete();
        }
    }

    public function confirmDeleteFolder(int $folderId): void
    {
        $folder = $this->activeFolderForManagement($folderId);
        $this->confirmedDeleteFolderId = $folder->id;
    }

    public function deleteFolder(int $folderId): void
    {
        abort_unless($this->confirmedDeleteFolderId === $folderId, 403);
        $folder = $this->activeFolderForManagement($folderId);

        DB::transaction(fn () => $folder->delete());
        Storage::disk('local')->deleteDirectory('secret-archive/'.$folder->id);
        session()->forget($this->unlockSessionKey($folder));
        $this->closeActiveFolder();
        $this->confirmedDeleteFolderId = null;
        $this->dispatch('toastr', type: 'success', message: __('ui.secret_archive_folder_deleted'));
    }

    public function confirmDeleteFile(int $fileId): void
    {
        $folder = $this->activeFolderForManagement();
        $folder->files()->findOrFail($fileId);
        $this->confirmedDeleteFileId = $fileId;
    }

    public function deleteFile(int $fileId): void
    {
        abort_unless($this->confirmedDeleteFileId === $fileId, 403);
        $folder = $this->activeFolderForManagement();
        $file = $folder->files()->findOrFail($fileId);

        Storage::disk('local')->delete($file->path);
        $file->delete();
        $this->confirmedDeleteFileId = null;
        $this->dispatch('toastr', type: 'success', message: __('ui.secret_archive_file_deleted'));
    }

    public function revealFolderPin(int $folderId): void
    {
        abort_unless($this->isSuperAdmin(), 403);
        $folder = $this->folderForCurrentAccount($folderId);

        $this->revealedFolderPin = Crypt::decryptString($folder->secret_encrypted);
        $this->revealedPinFolderId = $folder->id;
    }

    public function changeFolderPin(int $folderId): void
    {
        abort_unless($this->isSuperAdmin(), 403);
        $folder = $this->folderForCurrentAccount($folderId);

        $this->validate([
            'newFolderPin' => ['required', 'string', 'regex:/^\d{4,12}$/'],
        ], [
            'newFolderPin.regex' => __('ui.secret_archive_pin'),
        ]);

        $folder->forceFill([
            'secret_encrypted' => Crypt::encryptString($this->newFolderPin),
            'password_version' => $folder->password_version + 1,
        ])->save();

        session()->forget($this->unlockSessionKey($folder));
        session()->put($this->unlockSessionKey($folder), $folder->password_version);
        RateLimiter::clear('secret-archive-pin:'.Auth::id().':'.$folder->id);
        $this->revealedFolderPin = null;
        $this->revealedPinFolderId = null;
        $this->reset('newFolderPin');
        $this->dispatch('toastr', type: 'success', message: __('ui.secret_archive_pin_updated'));
    }

    public function isSuperAdmin(): bool
    {
        return SecretArchivePermissions::isSuperAdmin(Auth::user());
    }

    public function canManageCurrentAccount(): bool
    {
        return SecretArchivePermissions::canManage(Auth::user(), $this->account);
    }

    public function formatFileSize(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1).' MB'
            : number_format($bytes / 1024, 0).' KB';
    }

    private function folderForCurrentAccount(int $folderId): SecretArchiveFolder
    {
        $folder = SecretArchiveFolder::query()->findOrFail($folderId);
        abort_unless($folder->account === $this->account, 404);
        abort_unless(SecretArchivePermissions::canView(Auth::user(), $folder->account), 403);

        return $folder;
    }

    private function activeFolderForManagement(?int $folderId = null): SecretArchiveFolder
    {
        $folder = $this->folderForCurrentAccount($folderId ?? (int) $this->activeFolderId);
        abort_unless(SecretArchivePermissions::canManage(Auth::user(), $folder->account), 403);
        abort_unless($this->folderIsUnlocked($folder), 423);

        return $folder;
    }

    private function folderIsUnlocked(SecretArchiveFolder $folder): bool
    {
        return $this->isSuperAdmin()
            || (int) session($this->unlockSessionKey($folder), 0) === (int) $folder->password_version;
    }

    private function unlockSessionKey(SecretArchiveFolder $folder): string
    {
        return 'secret_archive.unlocks.'.$folder->id;
    }

    private function closeActiveFolder(): void
    {
        if ($this->activeFolderId && ! $this->isSuperAdmin()) {
            $activeFolder = SecretArchiveFolder::query()->find($this->activeFolderId);

            if ($activeFolder) {
                session()->forget($this->unlockSessionKey($activeFolder));
            }
        }

        $this->activeFolderId = null;
        $this->unlockPin = '';
        $this->revealedFolderPin = null;
        $this->revealedPinFolderId = null;
        $this->confirmedDeleteFolderId = null;
        $this->confirmedDeleteFileId = null;
        $this->reset('archiveFiles', 'newFolderPin');
        $this->resetValidation();
    }
}
