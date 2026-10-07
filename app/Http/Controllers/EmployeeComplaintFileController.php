<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class EmployeeComplaintFileController extends Controller
{
    public function __invoke(string $path)
    {
        $path = ltrim($path, '/');

        abort_unless(str_starts_with($path, 'employee-complaints/'), 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        $absolutePath = Storage::disk('public')->path($path);
        $mime = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => ResponseHeaderBag::DISPOSITION_INLINE.'; filename="'.basename($path).'"',
        ]);
    }
}
