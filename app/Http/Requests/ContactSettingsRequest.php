<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactSettingsRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('system-settings.update') ?? false; }
    public function rules(): array { return ['contact' => ['required', 'string', 'max:255']]; }
}
