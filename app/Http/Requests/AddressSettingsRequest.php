<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressSettingsRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('system-settings.update') ?? false; }
    public function rules(): array { return ['company_address' => ['required', 'string', 'max:1000']]; }
}
