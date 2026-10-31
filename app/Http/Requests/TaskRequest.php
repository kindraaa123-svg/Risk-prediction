<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->isMethod('post') ? 'tasks.create' : 'tasks.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'assignee_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['todo', 'in_progress', 'review', 'completed'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
