<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeTrackingController extends Controller
{
    /**
     * Display time tracking page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $project = null;

        // Project is required for time tracking
        if (!$request->filled('project_id')) {
            return redirect()
                ->route('projects.index')
                ->with('warning', 'Silakan pilih project untuk time tracking.');
        }

        $project = Project::findOrFail($request->project_id);

        // Check if user is member of this project
        if (!$user->isMemberOfProject($project)) {
            abort(403, 'Anda bukan anggota project ini.');
        }

        // Get available tasks - only tasks assigned to current user (not done/approved)
        $availableTasks = Task::where('project_id', $project->id)
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['done', 'done_approved'])
            ->orderBy('title')
            ->get();

        // Get running timer for current user
        $runningEntry = TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->running()
            ->with('task')
            ->first();

        // Get today's total hours for this project
        $todaySeconds = TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->today()
            ->completed()
            ->sum('duration_seconds');

        // Get this week's total hours for this project
        $weekSeconds = TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->thisWeek()
            ->completed()
            ->sum('duration_seconds');

        // Calculate daily average (based on last 7 days)
        $last7DaysSeconds = TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->where('started_at', '>=', now()->subDays(7))
            ->completed()
            ->sum('duration_seconds');
        $avgDailySeconds = $last7DaysSeconds / 7;

        // Get recent time entries
        $recentEntries = TimeEntry::forProject($project->id)
            ->completed()
            ->with(['task', 'user'])
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        return view('time-tracking.index', compact(
            'project',
            'availableTasks',
            'runningEntry',
            'todaySeconds',
            'weekSeconds',
            'avgDailySeconds',
            'recentEntries'
        ));
    }

    /**
     * Start a timer for a task.
     */
    public function start(Request $request): RedirectResponse
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
        ]);

        $user = auth()->user();
        $task = Task::findOrFail($request->task_id);
        $project = $task->project;

        // Check if user is member of this project
        if (!$user->isMemberOfProject($project)) {
            abort(403, 'Anda bukan anggota project ini.');
        }

        // Check if user is assignee of this task
        if ($task->assigned_to !== $user->id) {
            abort(403, 'Anda hanya dapat melacak waktu untuk task yang ditugaskan kepada Anda.');
        }

        // Stop any running timer for this user in this project
        TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->running()
            ->each(function ($entry) {
                $entry->stop();
            });

        // Create new time entry
        TimeEntry::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'started_at' => now(),
            'is_running' => true,
        ]);

        return redirect()
            ->route('time-tracking.index', ['project_id' => $project->id])
            ->with('success', 'Timer dimulai untuk: ' . $task->title);
    }

    /**
     * Stop a running timer.
     */
    public function stop(TimeEntry $timeEntry): RedirectResponse
    {
        $user = auth()->user();

        // Check ownership
        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Anda tidak dapat menghentikan timer orang lain.');
        }

        $timeEntry->stop();

        // Auto-update task status from todo to in_progress
        $task = $timeEntry->task;
        if ($task->status->value === 'todo') {
            $task->update(['status' => 'in_progress']);
        }

        return redirect()
            ->route('time-tracking.index', ['project_id' => $timeEntry->task->project_id])
            ->with('success', 'Timer dihentikan. Durasi: ' . $timeEntry->formatted_duration);
    }

    /**
     * Store a manual time entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'description' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $task = Task::findOrFail($request->task_id);
        $project = $task->project;

        // Check if user is member of this project
        if (!$user->isMemberOfProject($project)) {
            abort(403, 'Anda bukan anggota project ini.');
        }

        // Check if user is assignee of this task
        if ($task->assigned_to !== $user->id) {
            abort(403, 'Anda hanya dapat melacak waktu untuk task yang ditugaskan kepada Anda.');
        }

        $startedAt = \Carbon\Carbon::parse($request->started_at);
        $endedAt = \Carbon\Carbon::parse($request->ended_at);
        $durationSeconds = $startedAt->diffInSeconds($endedAt);

        TimeEntry::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $durationSeconds,
            'description' => $request->description,
            'is_running' => false,
        ]);

        // Auto-update task status from todo to in_progress
        if ($task->status->value === 'todo') {
            $task->update(['status' => 'in_progress']);
        }

        return redirect()
            ->route('time-tracking.index', ['project_id' => $project->id])
            ->with('success', 'Waktu berhasil dicatat.');
    }

    /**
     * Delete a time entry.
     */
    public function destroy(TimeEntry $timeEntry): RedirectResponse
    {
        $user = auth()->user();

        // Check ownership
        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Anda tidak dapat menghapus entri waktu orang lain.');
        }

        $projectId = $timeEntry->task->project_id;
        $timeEntry->delete();

        return redirect()
            ->route('time-tracking.index', ['project_id' => $projectId])
            ->with('success', 'Entri waktu berhasil dihapus.');
    }

    /**
     * Get current timer status (for AJAX updates).
     */
    public function status(Request $request): JsonResponse
    {
        $user = auth()->user();
        $projectId = $request->project_id;

        $runningEntry = TimeEntry::forUser($user->id)
            ->forProject($projectId)
            ->running()
            ->with('task:id,title')
            ->first();

        if (!$runningEntry) {
            return response()->json(['running' => false]);
        }

        $elapsedSeconds = $runningEntry->started_at->diffInSeconds(now());

        return response()->json([
            'running' => true,
            'task_id' => $runningEntry->task_id,
            'task_title' => $runningEntry->task->title,
            'started_at' => $runningEntry->started_at->toIso8601String(),
            'elapsed_seconds' => $elapsedSeconds,
        ]);
    }
}
