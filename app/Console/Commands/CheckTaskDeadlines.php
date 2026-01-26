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

        // Get tasks that are not done, have due date tomorrow, and have assignees
        $tasks = \App\Models\Task::where('status', '!=', \App\Enums\TaskStatus::DONE)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $upcomingDeadline)
            ->where('due_date', '>', now())
            ->whereHas('assignees') // Only tasks with at least one assignee
            ->with(['assignees', 'project'])
            ->get();

        $count = 0;
        foreach ($tasks as $task) {
            // Notify all assignees for this task
            foreach ($task->assignees as $assignee) {
                // Check if we already notified this user for this task (to avoid spam)
                $cacheKey = 'task_deadline_notified_' . $task->id . '_' . $assignee->id;
                if (!cache()->has($cacheKey)) {
                    $assignee->notify(new \App\Notifications\TaskDeadlineWarning($task));
                    cache()->put($cacheKey, true, now()->addDay());
                    $count++;
                }
            }
        }

        $this->info("Notifications sent for {$count} assignees.");
    }
}
