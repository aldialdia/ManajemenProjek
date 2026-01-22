<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get project end_date for validation
        $projectId = $this->input('project_id') ?? $this->route('task')?->project_id;
        $project = Project::find($projectId);
        $projectEndDate = $project?->end_date?->format('Y-m-d');

        $dueDateRules = ['required', 'date', 'after_or_equal:today'];

        // Add project deadline validation if project has end_date
        if ($projectEndDate) {
            $dueDateRules[] = 'before_or_equal:' . $projectEndDate;
        }

        // Add parent task deadline validation if this is a subtask
        $parentTaskId = $this->input('parent_task_id');
        if ($parentTaskId) {
            $parentTask = \App\Models\Task::find($parentTaskId);
            if ($parentTask && $parentTask->due_date) {
                $dueDateRules[] = 'before_or_equal:' . $parentTask->due_date->format('Y-m-d');
            }
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'project_id' => ['required', 'exists:projects,id'],
            'assigned_to' => ['required', 'exists:users,id'],
            'parent_task_id' => ['nullable', 'exists:tasks,id'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'due_date' => $dueDateRules,
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'project_id' => 'project',
            'assigned_to' => 'assignee',
            'due_date' => 'due date',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        // Get project end_date for error message
        $projectId = $this->input('project_id') ?? $this->route('task')?->project_id;
        $project = Project::find($projectId);
        $projectEndDate = $project?->end_date?->format('d M Y');

        // Get parent task due_date for error message
        $parentTaskId = $this->input('parent_task_id');
        $parentTask = $parentTaskId ? \App\Models\Task::find($parentTaskId) : null;
        $parentDueDate = $parentTask?->due_date?->format('d M Y');

        $beforeOrEqualMsg = 'Due date tidak boleh melebihi deadline';
        if ($parentDueDate) {
            $beforeOrEqualMsg .= ' tugas utama (' . $parentDueDate . ')';
        } elseif ($projectEndDate) {
            $beforeOrEqualMsg .= ' project (' . $projectEndDate . ')';
        }

        return [
            'due_date.after_or_equal' => 'Due date tidak boleh sebelum hari ini.',
            'due_date.before_or_equal' => $beforeOrEqualMsg . '.',
        ];
    }
}
