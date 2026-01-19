<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Intervention Image - may show IDE warning if not installed
// @phpstan-ignore-next-line
use Intervention\Image\Laravel\Facades\Image;

class AttachmentController extends Controller
{
    /**
     * Compress image file if it's JPG/PNG and larger than 10MB.
     * Returns the path of the stored file and the new file size.
     */
    private function storeAndCompressFile($file, string $storagePath): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $originalSize = $file->getSize();
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes

        // Check if file is a compressible image AND larger than 10MB
        $compressibleTypes = ['jpg', 'jpeg', 'png'];
        $compressibleMimes = ['image/jpeg', 'image/jpg', 'image/png'];

        $isCompressibleImage = in_array($extension, $compressibleTypes) || in_array($mimeType, $compressibleMimes);

        // Only compress if it's an image AND file size > 10MB
        if ($isCompressibleImage && $originalSize > $maxSize) {
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

                // Get new file size
                $newSize = Storage::disk('public')->size($fullPath);

                return [
                    'path' => $fullPath,
                    'size' => $newSize,
                    'mime_type' => $mimeType,
                ];
            } catch (\Exception $e) {
                // If compression fails, fall back to normal storage
                $path = $file->store($storagePath, 'public');
                return [
                    'path' => $path,
                    'size' => $originalSize,
                    'mime_type' => $mimeType,
                ];
            }
        }

        // Files under 10MB or non-image files, store normally
        $path = $file->store($storagePath, 'public');
        return [
            'path' => $path,
            'size' => $originalSize,
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Store a new attachment for a task.
     */
    public function storeForTask(Request $request, Task $task): RedirectResponse
    {
        // Authorization: User harus member dari project task ini
        $project = $task->project;
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isMemberOfProject($project)) {
            abort(403, 'Anda tidak memiliki akses ke task ini.');
        }

        // Allowed file extensions
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'png', 'jpg', 'jpeg', 'gif', 'txt', 'zip', 'rar', 'sql', 'js', 'php', 'html', 'css', 'json', 'py'];

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // Max 10MB
                function ($attribute, $value, $fail) use ($allowedExtensions) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('Format file tidak diizinkan. Format yang diizinkan: ' . implode(', ', $allowedExtensions));
                    }
                },
            ],
        ]);

        $file = $request->file('file');
        $fileData = $this->storeAndCompressFile($file, 'attachments/tasks/' . $task->id);

        $task->attachments()->create([
            'filename' => $file->getClientOriginalName(),
            'path' => $fileData['path'],
            'mime_type' => $fileData['mime_type'],
            'size' => $fileData['size'],
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Lampiran berhasil ditambahkan.');
    }

    /**
     * Store a new attachment for a project.
     */
    public function storeForProject(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:file,link',
            'file' => 'required_if:type,file|file|max:10240', // Max 10MB
            'link_url' => 'required_if:type,link|nullable|url',
            'link_name' => 'required_if:type,link|nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $fileData = $this->storeAndCompressFile($file, 'attachments/projects/' . $project->id);

        $project->attachments()->create([
            'filename' => $file->getClientOriginalName(),
            'path' => $fileData['path'],
            'mime_type' => $fileData['mime_type'],
            'size' => $fileData['size'],
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Lampiran berhasil ditambahkan.');
    }

    /**
     * Download an attachment.
     */
    public function download(Attachment $attachment)
    {
        if ($attachment->isLink()) {
            return redirect()->away($attachment->path);
        }

        $filePath = storage_path('app/public/' . $attachment->path);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($filePath, $attachment->filename);
    }

    /**
     * Delete an attachment.
     */
    public function destroy(Attachment $attachment): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $canDelete = false;

        // Uploader can always delete their own files
        if ($attachment->uploaded_by === $user->id) {
            $canDelete = true;
        }

        // Get the project from the attachment's parent (task or project)
        $project = null;
        if ($attachment->attachable_type === 'App\\Models\\Task') {
            $task = $attachment->attachable;
            $project = $task->project;

            // Task assignee can delete attachments on their task
            if ($task->assigned_to === $user->id) {
                $canDelete = true;
            }
        } elseif ($attachment->attachable_type === 'App\\Models\\Project') {
            $project = $attachment->attachable;
        }

        // Project manager/admin can delete any attachment in the project
        if ($project && $user->isManagerInProject($project)) {
            $canDelete = true;
        }

        if (!$canDelete) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus file ini.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }
}
