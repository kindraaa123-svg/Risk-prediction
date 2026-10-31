<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SystemSettingsRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('system-settings.update') ?? false; }

    public function rules(): array
    {
        return [
            'website_name' => ['required', 'string', 'max:150'],
            'contact' => ['required', 'string', 'max:255'],
            'company_address' => ['required', 'string', 'max:1000'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:1024'],
        ];
    }
}
