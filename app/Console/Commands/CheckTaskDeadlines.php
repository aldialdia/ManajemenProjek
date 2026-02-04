<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckTaskDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-task-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for tasks and projects due tomorrow (H-1) and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $upcomingDeadline = now()->addDay();

        // === TASK DEADLINE NOTIFICATIONS ===
        $tasks = \App\Models\Task::where('status', '!=', \App\Enums\TaskStatus::DONE)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $upcomingDeadline)
            ->where('due_date', '>', now())
            ->whereHas('assignees') // Tasks with at least one assignee
            ->whereHas('project', function($query) {
                // Skip tasks from on_hold projects
                $query->where('status', '!=', \App\Enums\ProjectStatus::ON_HOLD);
            })
            ->with(['assignees', 'project'])
            ->get();

        $taskCount = 0;
        foreach ($tasks as $task) {
            // Notify all assignees for this task
            foreach ($task->assignees as $assignee) {
                $cacheKey = 'task_deadline_notified_' . $task->id . '_' . $assignee->id;
                if (!cache()->has($cacheKey)) {
                    $assignee->notify(new \App\Notifications\TaskDeadlineWarning($task));
                    cache()->put($cacheKey, true, now()->addDay());
                    $taskCount++;
                }
            }
        }

        // === PROJECT DEADLINE NOTIFICATIONS ===
        $projects = \App\Models\Project::where('status', '!=', \App\Enums\ProjectStatus::DONE)
            ->where('status', '!=', \App\Enums\ProjectStatus::ON_HOLD) // Skip on_hold projects
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $upcomingDeadline)
            ->where('end_date', '>', now())
            ->with('users')
            ->get();

        $projectCount = 0;
        foreach ($projects as $project) {
            $cacheKey = 'project_deadline_notified_' . $project->id;
            if (!cache()->has($cacheKey)) {
                // Notify all project members
                foreach ($project->users as $user) {
                    $user->notify(new \App\Notifications\ProjectDeadlineWarning($project));
                }
                cache()->put($cacheKey, true, now()->addDay());
                $projectCount++;
            }
        }

        // === TIME TRACKING OVERDUE NOTIFICATIONS ===
        // Find timers running for more than 24 hours
        $overdueTimers = \App\Models\TimeEntry::where('is_running', true)
            ->where('started_at', '<', now()->subHours(24))
            ->with(['user', 'task'])
            ->get();

        $timerCount = 0;
        foreach ($overdueTimers as $timeEntry) {
            $cacheKey = 'timer_overdue_notified_' . $timeEntry->id;
            if (!cache()->has($cacheKey) && $timeEntry->user) {
                $timeEntry->user->notify(new \App\Notifications\TimeTrackingOverdue($timeEntry));
                cache()->put($cacheKey, true, now()->addHours(6)); // Remind again after 6 hours
                $timerCount++;
            }
        }

        $this->info("Notifications sent for {$taskCount} task assignees, {$projectCount} projects, and {$timerCount} overdue timers.");
    }
}
