<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
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
        // Exclude done_approved - it can only be set via approve route
        $allowedStatuses = ['todo', 'in_progress', 'review', 'done'];

        return [
            'status' => ['required', 'in:' . implode(',', $allowedStatuses)],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Status tidak valid. Status yang diizinkan: todo, in_progress, review, done.',
        ];
    }
}
