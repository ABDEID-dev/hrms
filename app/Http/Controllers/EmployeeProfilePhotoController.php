<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\Storage;

class EmployeeProfilePhotoController extends Controller
{
    public function __invoke(Employee $employee)
    {
        $path = $employee->user?->profile_photo_path ?: $employee->profile_photo_path;
        $path = $this->existingPhotoPath($path);
        $absolutePath = Storage::disk('public')->exists($path)
            ? Storage::disk('public')->path($path)
            : public_path('assets/img/avatars/1.png');

        return response()->file($absolutePath, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function existingPhotoPath(?string $path): string
    {
        $defaultPath = 'profile-photos/.default-photo.jpg';

        if ($path && Storage::disk('public')->exists($path)) {
            return $path;
        }

        if (Storage::disk('public')->exists($defaultPath)) {
            return $defaultPath;
        }

        return 'profile-photos/.default-photo.jpg';
    }
}
