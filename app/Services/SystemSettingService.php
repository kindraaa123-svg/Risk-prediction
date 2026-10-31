<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Http\UploadedFile;

class SystemSettingService
{
    private const DEFAULTS = [
        'website_name' => 'Kindra Risk Prediction',
        'website_short_name' => 'Kindra',
        'website_description' => 'An intelligent project management system with delay risk prediction.',
        'website_tagline' => 'Manage Smarter. Predict Earlier.',
        'contact' => 'support@kindra.local',
        'contact_email' => '',
        'contact_phone' => '',
        'contact_whatsapp' => '',
        'contact_person' => '',
        'company_address' => 'Jl. Ahmad Yani No. 10, Batam, Kepulauan Riau',
        'company_city' => '',
        'company_province' => '',
        'company_postal_code' => '',
        'company_country' => 'Indonesia',
    ];

    public function all(): array
    {
        $saved = SystemSetting::query()->pluck('value', 'key')->all();

        return array_merge(self::DEFAULTS, $saved);
    }

    public function public(): array
    {
        $values = $this->all();
        $logo = SystemSetting::query()->where('key', 'website_logo')->first();
        $favicon = SystemSetting::query()->where('key', 'website_favicon')->first();
        $logoMedia = $logo?->getFirstMedia('setting-media');
        $faviconMedia = $favicon?->getFirstMedia('setting-media');

        return [
            'websiteName' => $values['website_name'],
            'websiteShortName' => $values['website_short_name'],
            'websiteDescription' => $values['website_description'],
            'websiteTagline' => $values['website_tagline'],
            'logoUrl' => $logo?->value ?: ($logoMedia ? route('system-media', $logoMedia->getKey()) : null),
            'faviconUrl' => $favicon?->value ?: ($faviconMedia ? route('system-media', $faviconMedia->getKey()) : null),
            'contactEmail' => $values['contact'] ?? $values['contact_email'],
            'contactPhone' => $values['contact_phone'],
            'contactWhatsapp' => $values['contact_whatsapp'],
            'companyAddress' => $values['company_address'],
            'companyCity' => $values['company_city'],
            'companyProvince' => $values['company_province'],
            'companyPostalCode' => $values['company_postal_code'],
            'companyCountry' => $values['company_country'],
        ];
    }

    public function update(array $values, ?string $group = null): void
    {
        foreach ($values as $key => $value) {
            SystemSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        activity('system-settings')
            ->causedBy(auth()->user())
            ->withProperties(['settings' => array_keys($values), 'group' => $group])
            ->log('Updated '.($group ?? 'system').' settings');
    }

    public function putMedia(string $key, UploadedFile $file): void
    {
        $setting = SystemSetting::query()->updateOrCreate(['key' => $key], ['value' => null]);
        $media = $setting->addMedia($file)->toMediaCollection('setting-media');
        $setting->update(['value' => $media->getUrl()]);

        activity('system-settings')->causedBy(auth()->user())->withProperties(['setting' => $key])->log(ucwords(str_replace('_', ' ', $key)).' updated');
    }

    public function removeMedia(string $key): void
    {
        $setting = SystemSetting::query()->where('key', $key)->first();
        if ($setting) {
            $setting->clearMediaCollection('setting-media');
            $setting->update(['value' => null]);
        }

        activity('system-settings')->causedBy(auth()->user())->withProperties(['setting' => $key])->log(ucwords(str_replace('_', ' ', $key)).' removed');
    }
}
