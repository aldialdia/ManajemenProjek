<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;

class DocumentController extends Controller
{
    /**
     * Compress image file if it's JPG/PNG and larger than 10MB.
     * Returns the path of the stored file.
     */
    private function storeAndCompressFile($file, string $storagePath): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes

        // Check if file is a compressible image AND larger than 10MB
        $compressibleTypes = ['jpg', 'jpeg', 'png'];
        $compressibleMimes = ['image/jpeg', 'image/jpg', 'image/png'];

        $isCompressibleImage = in_array($extension, $compressibleTypes) || in_array($mimeType, $compressibleMimes);

        // Only compress if it's an image AND file size > 10MB
        if ($isCompressibleImage && $fileSize > $maxSize) {
            try {
                // Generate unique filename
                $filename = uniqid() . '_' . time() . '.' . $extension;
                $fullPath = $storagePath . '/' . $filename;

                // Read and compress image
                $image = Image::read($file->getRealPath());

                // Resize if too large (max 1920px width)
                if ($image->width() > 1920) {
                    $image->scale(width: 1920);
                }

                // Encode with compression
                if (in_array($extension, ['jpg', 'jpeg']) || $mimeType === 'image/jpeg') {
                    $encoded = $image->toJpeg(75); // 75% quality
                } else {
                    $encoded = $image->toPng(); // PNG compression
                }

                // Store compressed image
                Storage::disk('public')->put($fullPath, (string) $encoded);

                return $fullPath;
            } catch (\Exception $e) {
                // If compression fails, fall back to normal storage
                return $file->store($storagePath, 'public');
            }
        }

        // Files under 10MB or non-image files, store normally
        return $file->store($storagePath, 'public');
    }

    /**
     * Display a listing of documents for a project.
     * Includes both Documents (versioned) and Task Attachments.
     */
    public function index(Project $project): View
    {
        // Get versioned documents (shared with all project members)
        $documents = $project->documents()->with(['latestVersion.uploader', 'project'])->latest()->get();

        // Get ALL task attachments from ALL tasks in this project
        // All project members can see all attachments
        $taskAttachments = \App\Models\Attachment::whereHasMorph('attachable', [\App\Models\Task::class], function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })
            ->with(['uploader', 'attachable'])->latest()->get();

        // Transform attachments to match document structure for unified display
        $attachmentDocs = $taskAttachments->map(function ($att) use ($project) {
            return (object) [
                'id' => 'att-' . $att->id,
                'title' => $att->filename,
                'type' => 'file',
                'project' => $project,
                'updated_at' => $att->created_at,
                'latestVersion' => (object) [
                    'version_number' => 1,
                    'file_path' => $att->path,
                    'uploader' => $att->uploader,
                    'getSizeAttribute' => fn() => $att->human_size,
                ],
                'is_attachment' => true,
                'attachment_id' => $att->id,
                'source_task' => $att->attachable,
            ];
        });

        // Convert Eloquent Collection to base Collection to avoid getKey() issues when merging
        $allDocuments = collect($documents->all())
            ->concat($attachmentDocs)
            ->sortByDesc('updated_at')
            ->values();

        return view('documents.index', ['project' => $project, 'documents' => $allDocuments]);
    }

    /**
     * Show form to create a new document.
     */
    public function create(Request $request, Project $project): View
    {
        $fromOverview = $request->query('from_overview', false);
        return view('documents.create', compact('project', 'fromOverview'));
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request, Project $project)
    {
        // Check if project is on_hold - only manager can upload
        if ($project->isOnHold() && !auth()->user()->isManagerInProject($project)) {
            abort(403, 'Project sedang ditunda. Anda tidak dapat mengupload dokumen.');
        }

        // Allowed file extensions
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'png', 'jpg', 'jpeg', 'gif', 'txt', 'zip', 'rar', 'sql', 'js', 'php', 'html', 'css', 'json', 'py'];

        // Compressible image types can be larger (will be auto-compressed)
        $compressibleTypes = ['jpg', 'jpeg', 'png'];

        $request->validate([
            'title' => 'nullable|string|max:255',
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) use ($allowedExtensions, $compressibleTypes) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    $fileSize = $value->getSize();
                    $maxImageSize = 50 * 1024 * 1024; // 50MB for compressible images
                    $maxOtherSize = 10 * 1024 * 1024; // 10MB for other files
        
                    // Check extension first
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('Format file tidak diizinkan. Format yang diizinkan: ' . implode(', ', $allowedExtensions));
                        return;
                    }

                    // Check size based on file type
                    if (in_array($extension, $compressibleTypes)) {
                        // Images up to 50MB allowed (will be compressed)
                        if ($fileSize > $maxImageSize) {
                            $fail('File gambar maksimal 50MB (akan dikompres otomatis jika > 10MB).');
                        }
                    } else {
                        // Other files max 10MB
                        if ($fileSize > $maxOtherSize) {
                            $fail('File maksimal 10MB untuk format ini.');
                        }
                    }
                },
            ],
            'changelog' => 'nullable|string',
        ]);

        $file = $request->file('file');

        // Use original filename as title if not provided
        $title = $request->title ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $path = $this->storeAndCompressFile($file, 'documents/' . $project->id);

        // Check if upload is from overview
        $fromOverview = $request->input('from_overview', false);

        // 1. Create Document
        $document = $project->documents()->create([
            'title' => $title,
            'type' => 'file',
            'show_in_overview' => $fromOverview ? true : false,
        ]);

        // 2. Create Version 1
        $version = $document->versions()->create([
            'file_path' => $path,
            'version_number' => 1,
            'changelog' => $request->changelog ?? 'Initial upload',
            'uploaded_by' => auth()->id(),
        ]);

        // 3. Update Snapshot
        $document->update(['latest_version_id' => $version->id]);

        // Redirect based on source
        if ($fromOverview) {
            return redirect()->route('projects.show', $project)
                ->with('success', 'Dokumen berhasil diupload.');
        }

        return redirect()->route('projects.documents.index', $project)
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display a specific document and its history.
     */
    public function show(Document $document): View
    {
        $document->load([
            'project',
            'versions.uploader',
            'versions' => function ($query) {
                $query->orderBy('version_number', 'desc');
            }
        ]);

        return view('documents.show', compact('document'));
    }

    /**
     * Store a new version of an existing document.
     */
    public function storeVersion(Request $request, Document $document)
    {
        // Check if project is on_hold - only manager can upload
        $project = $document->project;
        if ($project->isOnHold() && !auth()->user()->isManagerInProject($project)) {
            abort(403, 'Project sedang ditunda. Anda tidak dapat mengupload dokumen.');
        }

        // Allowed file extensions
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'png', 'jpg', 'jpeg', 'gif', 'txt', 'zip', 'rar', 'sql', 'js', 'php', 'html', 'css', 'json', 'py'];

        // Compressible image types can be larger (will be auto-compressed)
        $compressibleTypes = ['jpg', 'jpeg', 'png'];

        $request->validate([
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) use ($allowedExtensions, $compressibleTypes) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    $fileSize = $value->getSize();
                    $maxImageSize = 50 * 1024 * 1024; // 50MB for compressible images
                    $maxOtherSize = 10 * 1024 * 1024; // 10MB for other files
        
                    // Check extension first
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('Format file tidak diizinkan. Format yang diizinkan: ' . implode(', ', $allowedExtensions));
                        return;
                    }

                    // Check size based on file type
                    if (in_array($extension, $compressibleTypes)) {
                        // Images up to 50MB allowed (will be compressed)
                        if ($fileSize > $maxImageSize) {
                            $fail('File gambar maksimal 50MB (akan dikompres otomatis jika > 10MB).');
                        }
                    } else {
                        // Other files max 10MB
                        if ($fileSize > $maxOtherSize) {
                            $fail('File maksimal 10MB untuk format ini.');
                        }
                    }
                },
            ],
            'changelog' => 'required|string',
        ]);

        $currentVersion = $document->latestVersion->version_number ?? 0;
        $newVersionNum = $currentVersion + 1;

        $file = $request->file('file');
        $path = $this->storeAndCompressFile($file, 'documents/' . $document->project_id);

        $version = $document->versions()->create([
            'file_path' => $path,
            'version_number' => $newVersionNum,
            'changelog' => $request->changelog,
            'uploaded_by' => auth()->id(),
        ]);

        $document->update(['latest_version_id' => $version->id]);

        return back()->with('success', 'New version uploaded successfully.');
    }

    /**
     * Download a specific version.
     */
    public function download(DocumentVersion $version)
    {
        if (!Storage::disk('public')->exists($version->file_path)) {
            return back()->with('error', 'File not found on server.');
        }

        return Storage::disk('public')->download($version->file_path, $version->document->title . '-v' . $version->version_number . '.' . pathinfo($version->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Delete a document and all its versions.
     */
    public function destroy(Document $document)
    {
        $projectId = $document->project_id;

        // Delete all version files from storage
        foreach ($document->versions as $version) {
            if ($version->file_path && Storage::disk('public')->exists($version->file_path)) {
                Storage::disk('public')->delete($version->file_path);
            }
        }

        // Delete the document (versions will cascade delete)
        $document->delete();

        return redirect()->route('projects.documents.index', $projectId)
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    /**
     * Delete a specific version of a document.
     */
    public function destroyVersion(DocumentVersion $version)
    {
        $document = $version->document;

        // If this is the only version, delete the entire document
        if ($document->versions()->count() <= 1) {
            return $this->destroy($document);
        }

        // Delete the file from storage
        if ($version->file_path && Storage::disk('public')->exists($version->file_path)) {
            Storage::disk('public')->delete($version->file_path);
        }

        // If this was the latest version, update the document's latest_version_id
        if ($document->latest_version_id === $version->id) {
            $newLatest = $document->versions()->where('id', '!=', $version->id)->orderBy('version_number', 'desc')->first();
            $document->update(['latest_version_id' => $newLatest?->id]);
        }

        $version->delete();

        return back()->with('success', 'Versi dokumen berhasil dihapus.');
    }
}
