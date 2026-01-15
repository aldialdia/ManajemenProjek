<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Store a new attachment for a task.
     */
    public function storeForTask(Request $request, Task $task): RedirectResponse
    {
        // Authorization: User harus member dari project task ini
        $project = $task->project;
        if (!auth()->user()->isMemberOfProject($project)) {
            abort(403, 'Anda tidak memiliki akses ke task ini.');
        }

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // Max 10MB
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,gif,txt,zip,rar', // Whitelist ekstensi
            ],
        ]);

        $file = $request->file('file');

        // Generate nama file yang aman untuk mencegah path traversal
        $safeName = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('attachments/tasks/' . $task->id, $safeName, 'public');

        $task->attachments()->create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
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

        if ($request->type === 'link') {
            $project->attachments()->create([
                'filename' => $request->link_name,
                'path' => $request->link_url,
                'mime_type' => 'external-link',
                'size' => 0,
                'uploaded_by' => auth()->id(),
            ]);
        } else {
            $file = $request->file('file');
            $path = $file->store('attachments/projects/' . $project->id, 'public');

            $project->attachments()->create([
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }

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

        if (!Storage::disk('public')->exists($attachment->path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($attachment->path, $attachment->filename);
    }

    /**
     * Delete an attachment.
     */
    public function destroy(Attachment $attachment): RedirectResponse
    {
        $user = auth()->user();
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
