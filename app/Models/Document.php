<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'project_id',
        'type',
        'content',
        'latest_version_id',
        'show_in_overview',
    ];

    /**
     * Get the project that owns the document.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the versions for the document.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Get the latest version of the document.
     */
    public function latestVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'latest_version_id');
    }

    /**
     * Check if document is a file based document.
     */
    public function isFile(): bool
    {
        return $this->type === 'file';
    }
}
