<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\TimeTrackingLog;
use App\Notifications\TimeTrackingOverdue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->whereNotIn('status', ['done', 'done_approved'])
            ->orderBy('title')
            ->get();

        // Get running or paused timer for current user
        $runningEntry = TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->active()
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

        // Get recent activity logs for this project
        $recentLogs = TimeTrackingLog::whereHas('task', function ($q) use ($project) {
            $q->where('project_id', $project->id);
        })
            ->with(['task', 'user'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Get total time for this project (all time)
        $totalSeconds = TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->completed()
            ->sum('duration_seconds');

        // Check and notify if user has overdue timer (running > 24 hours)
        $this->checkAndNotifyOverdueTimer($user);

        return view('time-tracking.index', compact(
            'project',
            'availableTasks',
            'runningEntry',
            'todaySeconds',
            'weekSeconds',
            'avgDailySeconds',
            'recentEntries',
            'recentLogs',
            'totalSeconds'
        ));
    }

    /**
     * Check and send notification for overdue timer (running > 24 hours).
     */
    protected function checkAndNotifyOverdueTimer($user): void
    {
        // Find timers running for more than 24 hours for this user
        $overdueTimers = TimeEntry::forUser($user->id)
            ->where('is_running', true)
            ->where('started_at', '<', now()->subHours(24))
            ->with('task')
            ->get();

        foreach ($overdueTimers as $timeEntry) {
            // Use cache to prevent duplicate notifications (remind every 6 hours)
            $cacheKey = 'timer_overdue_notified_' . $timeEntry->id . '_' . now()->format('Y-m-d-H');
            $cacheKeyBase = 'timer_overdue_notified_base_' . $timeEntry->id;
            
            // Only notify once every 6 hours
            if (!Cache::has($cacheKeyBase)) {
                $user->notify(new TimeTrackingOverdue($timeEntry));
                Cache::put($cacheKeyBase, true, now()->addHours(6));
            }
        }
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

        // If project is on_hold, only manager can start timer
        if ($project->isOnHold() && !$user->isManagerInProject($project)) {
            abort(403, 'Project sedang ditunda. Anda tidak dapat melacak waktu.');
        }

        // Check if user is assignee of this task
        if (!$task->isAssignedTo($user)) {
            abort(403, 'Anda hanya dapat melacak waktu untuk task yang ditugaskan kepada Anda.');
        }

        // Stop any running timer for this user in this project
        TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->active()
            ->each(function ($entry) {
                $entry->stop();
            });

        // Create new time entry
        $timeEntry = TimeEntry::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'started_at' => now(),
            'is_running' => true,
        ]);

        // Create log
        TimeTrackingLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'time_entry_id' => $timeEntry->id,
            'action' => 'started',
            'duration_at_action' => 0,
        ]);

        // Auto-update project status from new to in_progress
        $project->startIfNew();

        // Auto-update task status from todo to in_progress
        if ($task->status->value === 'todo') {
            $task->update(['status' => 'in_progress']);
            // Update project status based on task changes
            $project->checkAndUpdateStatusBasedOnTasks();
        }

        return redirect()
            ->route('time-tracking.index', ['project_id' => $project->id])
            ->with('success', 'Timer dimulai untuk: ' . $task->title);
    }

    /**
     * Start a timer from task detail page.
     */
    public function startFromTask(Task $task): RedirectResponse
    {
        $user = auth()->user();
        $project = $task->project;

        // Check if user is member of this project
        if (!$user->isMemberOfProject($project)) {
            abort(403, 'Anda bukan anggota project ini.');
        }

        // If project is on_hold, only manager can start timer
        if ($project->isOnHold() && !$user->isManagerInProject($project)) {
            abort(403, 'Project sedang ditunda. Anda tidak dapat melacak waktu.');
        }

        // Check if user is assignee of this task
        if (!$task->isAssignedTo($user)) {
            abort(403, 'Anda hanya dapat melacak waktu untuk task yang ditugaskan kepada Anda.');
        }

        // Stop any running timer for this user in this project
        TimeEntry::forUser($user->id)
            ->forProject($project->id)
            ->active()
            ->each(function ($entry) {
                $entry->stop();
            });

        // Create new time entry
        $timeEntry = TimeEntry::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'started_at' => now(),
            'is_running' => true,
        ]);

        // Create log
        TimeTrackingLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'time_entry_id' => $timeEntry->id,
            'action' => 'started',
            'duration_at_action' => 0,
        ]);

        // Auto-update project status from new to in_progress
        $project->startIfNew();

        // Auto-update task status from todo to in_progress
        if ($task->status->value === 'todo') {
            $task->update(['status' => 'in_progress']);
            // Update project status based on task changes
            $project->checkAndUpdateStatusBasedOnTasks();
        }

        return redirect()
            ->back()
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
            ->back()
            ->with('success', 'Timer dihentikan. Durasi: ' . $timeEntry->formatted_duration);
    }

    /**
     * Pause a running timer.
     */
    public function pause(TimeEntry $timeEntry): RedirectResponse
    {
        $user = auth()->user();

        // Check ownership
        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Anda tidak dapat menjeda timer orang lain.');
        }

        if (!$timeEntry->is_running) {
            return redirect()->back()->with('warning', 'Timer tidak sedang berjalan.');
        }

        $timeEntry->pause();

        return redirect()
            ->back()
            ->with('success', 'Timer dijeda. Durasi sementara: ' . gmdate('H:i:s', $timeEntry->current_elapsed_seconds));
    }

    /**
     * Resume a paused timer.
     */
    public function resume(TimeEntry $timeEntry): RedirectResponse
    {
        $user = auth()->user();

        // Check ownership
        if ($timeEntry->user_id !== $user->id) {
            abort(403, 'Anda tidak dapat melanjutkan timer orang lain.');
        }

        if (!$timeEntry->is_paused) {
            return redirect()->back()->with('warning', 'Timer tidak sedang dijeda.');
        }

        $timeEntry->resume();

        return redirect()
            ->back()
            ->with('success', 'Timer dilanjutkan.');
    }

    /**
     * Complete task and stop timer.
     */
    public function completeTask(Task $task): RedirectResponse
    {
        $user = auth()->user();
        $project = $task->project;

        // Check if user is assignee of this task
        if (!$task->isAssignedTo($user)) {
            abort(403, 'Anda hanya dapat menyelesaikan task yang ditugaskan kepada Anda.');
        }

        // Stop any active timer for this task
        $activeEntry = TimeEntry::forUser($user->id)
            ->forTask($task->id)
            ->active()
            ->first();

        if ($activeEntry) {
            $activeEntry->complete();
        }

        // Update task status to review (pending approval)
        $task->update(['status' => 'review']);

        // Update project status based on task changes
        $project->checkAndUpdateStatusBasedOnTasks();

        return redirect()
            ->back()
            ->with('success', 'Task ditandai sebagai selesai dan menunggu persetujuan.');
    }

    /**
     * Get activity logs for a task.
     */
    public function getTaskLogs(Task $task): JsonResponse
    {
        $user = auth()->user();

        // Check if user is member of this project
        if (!$user->isMemberOfProject($task->project)) {
            return response()->json(['error' => 'Anda bukan anggota project ini.'], 403);
        }

        $logs = TimeTrackingLog::forTask($task->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'action_label' => $log->action_label,
                    'action_icon' => $log->action_icon,
                    'action_color' => $log->action_color,
                    'duration' => $log->formatted_duration,
                    'user' => $log->user->name,
                    'created_at' => $log->created_at->format('d M Y H:i'),
                    'time_ago' => $log->created_at->diffForHumans(),
                ];
            });

        return response()->json($logs);
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

        // If project is on_hold, only manager can add manual time entry
        if ($project->isOnHold() && !$user->isManagerInProject($project)) {
            abort(403, 'Project sedang ditunda. Anda tidak dapat melacak waktu.');
        }

        // Check if user is assignee of this task
        if (!$task->isAssignedTo($user)) {
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

        // Check and notify if user has overdue timer (running > 24 hours)
        $this->checkAndNotifyOverdueTimer($user);

        $runningEntry = TimeEntry::forUser($user->id)
            ->forProject($projectId)
            ->active()
            ->with('task:id,title')
            ->first();

        if (!$runningEntry) {
            return response()->json(['running' => false, 'paused' => false]);
        }

        return response()->json([
            'running' => $runningEntry->is_running,
            'paused' => $runningEntry->is_paused,
            'entry_id' => $runningEntry->id,
            'task_id' => $runningEntry->task_id,
            'task_title' => $runningEntry->task->title,
            'started_at' => $runningEntry->started_at->toIso8601String(),
            'elapsed_seconds' => $runningEntry->current_elapsed_seconds,
            'paused_duration' => $runningEntry->paused_duration_seconds,
        ]);
    }

    /**
     * Get global timer status (for floating widget - no project_id required).
     */
    public function globalStatus(): JsonResponse
    {
        $user = auth()->user();

        $runningEntry = TimeEntry::forUser($user->id)
            ->active()
            ->with(['task:id,title,project_id', 'task.project:id,name'])
            ->first();

        if (!$runningEntry) {
            return response()->json(['running' => false]);
        }

        return response()->json([
            'running' => true,
            'entry_id' => $runningEntry->id,
            'task_id' => $runningEntry->task_id,
            'task_title' => $runningEntry->task->title,
            'project_name' => $runningEntry->task->project->name,
            'started_at' => $runningEntry->started_at->toIso8601String(),
            'elapsed_seconds' => $runningEntry->current_elapsed_seconds,
        ]);
    }
}

