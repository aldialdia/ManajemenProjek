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
    protected $description = 'Check for tasks due tomorrow (H-1) and send email + in-app notifications';

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
            ->whereNotNull('assigned_to')
            ->with('assignee')
            ->get();

        $taskCount = 0;
        foreach ($tasks as $task) {
            $cacheKey = 'task_deadline_notified_' . $task->id;
            if (!cache()->has($cacheKey)) {
                $task->assignee->notify(new \App\Notifications\TaskDeadlineWarning($task));
                cache()->put($cacheKey, true, now()->addDay());
                $taskCount++;
            }
        }

        // === PROJECT DEADLINE NOTIFICATIONS ===
        $projects = \App\Models\Project::whereNotNull('end_date')
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
        // Find timers running for more than 2 minutes (FOR TESTING - change back to 24 hours in production)
        $overdueTimers = \App\Models\TimeEntry::where('is_running', true)
            ->where('started_at', '<', now()->subHours(24))
            ->with(['user', 'task'])
            ->get();

        $timerCount = 0;
        foreach ($overdueTimers as $timeEntry) {
            $cacheKey = 'timer_overdue_notified_' . $timeEntry->id;
            if (!cache()->has($cacheKey) && $timeEntry->user) {
                $timeEntry->user->notify(new \App\Notifications\TimeTrackingOverdue($timeEntry));
                cache()->put($cacheKey, true, now()->addMinutes(2)); // Remind again after 2 minutes (FOR TESTING)
                $timerCount++;
            }
        }

        $this->info("Notifications sent for {$taskCount} tasks, {$projectCount} projects, and {$timerCount} overdue timers.");
    }
}
