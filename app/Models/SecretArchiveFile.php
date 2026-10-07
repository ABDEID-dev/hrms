<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecretArchiveFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'path',
        'original_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    protected $hidden = [
        'path',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(SecretArchiveFolder::class, 'folder_id');
    }
}
