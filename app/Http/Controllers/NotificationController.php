<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display all notifications.
     */
    public function index(Request $request): View
    {
        $query = auth()->user()->notifications();

        if ($request->filter === 'unread') {
            $query = auth()->user()->unreadNotifications();
        }

        $perPage = $request->input('per_page', 8);
        $notifications = $query->paginate($perPage)->appends($request->query());

        return view('notifications.index', compact('notifications', 'perPage'));
    }

    /**
     * Get unread notifications for dropdown (AJAX).
     */
    public function getUnread(): JsonResponse
    {
        $notifications = auth()->user()
            ->unreadNotifications()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'read_at' => $notification->read_at,
                ];
            });

        $unreadCount = auth()->user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Redirect based on notification type
        $data = $notification->data;

        if (isset($data['type']) && $data['type'] === 'project_invitation' && isset($data['invitation_token'])) {
            return redirect()->route('invitations.show', $data['invitation_token']);
        } elseif (isset($data['task_id'])) {
            // Get task with project info for sidebar auto-expand
            $task = \App\Models\Task::with('project')->find($data['task_id']);
            if ($task && $task->project) {
                session()->flash('expand_project', [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                    'status' => $task->project->status->value ?? 'new',
                ]);
            }
            return redirect()->route('tasks.show', $data['task_id']);
        } elseif (isset($data['project_id']) && $data['type'] === 'new_comment' && $data['target_type'] === 'project') {
            return redirect()->route('projects.show', $data['project_id']);
        } elseif (isset($data['project_id']) && $data['type'] === 'project_deadline_warning') {
            // Project deadline warning - redirect to project
            return redirect()->route('projects.show', $data['project_id']);
        } elseif (isset($data['project_id'])) {
            // Generic project notification
            return redirect()->route('projects.show', $data['project_id']);
        }

        return back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Notifikasi dihapus.');
    }
}
