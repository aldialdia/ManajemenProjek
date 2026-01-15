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
        
        $tasks = \App\Models\Task::where('status', '!=', \App\Enums\TaskStatus::DONE)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $upcomingDeadline)
            ->where('due_date', '>', now())
            ->whereNotNull('assigned_to')
            ->with('assignee')
            ->get();

        $count = 0;
        foreach ($tasks as $task) {
            // Check if we already notified recently to avoid spam (optional, but good practice)
            // For now, simpler implementation: just send it. Scheduler runs daily?
            // If scheduler runs hourly, we might spam.
            // Better logic: check if due_date is between 1 day ago and 1 day future, but practically "due date is tomorrow".
            // Implementation: We assume this command runs ONCE per day, or we need a flag 'notification_sent'.
            // Given constraints, I will assume it runs once daily or I'll add a cache check.
            
            $cacheKey = 'task_deadline_notified_' . $task->id;
            if (!cache()->has($cacheKey)) {
                $task->assignee->notify(new \App\Notifications\TaskDeadlineWarning($task));
                cache()->put($cacheKey, true, now()->addDay());
                $count++;
            }
        }

        $this->info("Notifications sent for {$count} tasks.");
    }
}
