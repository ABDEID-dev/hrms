<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecretArchiveFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'account',
        'name',
        'secret_encrypted',
        'password_version',
        'created_by',
    ];

    protected $hidden = [
        'secret_encrypted',
    ];

    protected $casts = [
        'password_version' => 'integer',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(SecretArchiveFile::class, 'folder_id');
    }
}
