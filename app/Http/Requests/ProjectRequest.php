<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->isMethod('post') ? 'projects.create' : 'projects.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', Rule::unique('projects', 'code')->ignore($this->route('project')?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'status' => ['required', Rule::in(['planning', 'active', 'completed', 'archived'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
