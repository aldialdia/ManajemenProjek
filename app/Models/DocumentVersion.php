<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'file_path',
        'content_snapshot',
        'version_number',
        'changelog',
        'uploaded_by',
    ];

    /**
     * Get the document that owns the version.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who uploaded the version.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the file size format.
     */
    public function getSizeAttribute(): string
    {
        // Ideally we should store size in DB, but for now we'll check file if possible or return N/A
        // For local files we can check:
        if ($this->file_path && \Illuminate\Support\Facades\Storage::exists($this->file_path)) {
            $bytes = \Illuminate\Support\Facades\Storage::size($this->file_path);
            $units = ['B', 'KB', 'MB', 'GB'];
            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                $bytes /= 1024;
            }
            return round($bytes, 2) . ' ' . $units[$i];
        }
        return 'N/A';
    }
}
