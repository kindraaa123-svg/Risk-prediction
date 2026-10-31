<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandingSettingsRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('system-settings.update') ?? false; }
    public function rules(): array { return ['logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'], 'favicon' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:1024']]; }
}
