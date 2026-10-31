<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('system-settings.update') ?? false; }
    public function rules(): array { return ['website_name' => ['required', 'string', 'max:150'], 'website_short_name' => ['nullable', 'string', 'max:50'], 'website_description' => ['nullable', 'string', 'max:1000'], 'website_tagline' => ['nullable', 'string', 'max:150']]; }
}
