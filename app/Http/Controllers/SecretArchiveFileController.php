<?php

namespace App\Http\Controllers;

use App\Models\SecretArchiveFile;
use App\Support\SecretArchivePermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;

class SecretArchiveFileController extends Controller
{
    public function view(SecretArchiveFile $file): BinaryFileResponse
    {
        $this->authorizeFileAccess($file);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($file->path), 404);

        $filename = basename(str_replace('\\', '/', $file->original_name));

        return response()->file($disk->path($file->path), [
            'Content-Type' => $file->mime ?: 'application/octet-stream',
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $filename,
                Str::ascii($filename) ?: 'archive-file'
            ),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function download(SecretArchiveFile $file): BinaryFileResponse
    {
        $this->authorizeFileAccess($file);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($file->path), 404);

        return response()->download($disk->path($file->path), basename(str_replace('\\', '/', $file->original_name)), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    private function authorizeFileAccess(SecretArchiveFile $file): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $folder = $file->folder;
        abort_unless($folder && SecretArchivePermissions::canView($user, $folder->account), 403);

        if (! SecretArchivePermissions::isSuperAdmin($user)) {
            $unlockedVersion = session('secret_archive.unlocks.'.$folder->id);
            abort_unless((int) $unlockedVersion === (int) $folder->password_version, 423);
        }
    }
}
