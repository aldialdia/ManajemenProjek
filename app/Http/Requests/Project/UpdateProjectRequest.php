<?php

namespace App\Http\Requests\Project;

use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'goals' => ['nullable', 'string'],
            'type' => ['nullable', Rule::enum(ProjectType::class)],
            'status' => ['nullable', Rule::enum(ProjectStatus::class)],
            'client_id' => ['nullable', 'exists:clients,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'users.*.exists' => 'One or more selected users do not exist.',
        ];
    }
}
