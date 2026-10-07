<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class EmployeeDocumentFileController extends Controller
{
    public function __invoke(EmployeeDocument $document)
    {
        abort_unless(auth()->user()?->can('view employee documents') || auth()->user()?->can('manage employee documents'), 403);
        abort_unless(Storage::disk('public')->exists($document->path), 404);

        $path = Storage::disk('public')->path($document->path);
        $name = $document->original_name ?: basename($document->path);
        $mime = $document->mime ?: Storage::disk('public')->mimeType($document->path);

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => ResponseHeaderBag::DISPOSITION_INLINE.'; filename="'.$name.'"',
        ]);
    }
}
