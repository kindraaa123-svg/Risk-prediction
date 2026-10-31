<?php

namespace App\Http\Middleware;

use App\Services\SystemSettingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $systemSettings = app(SystemSettingService::class)->public();
        return [
            ...parent::share($request),
            'app' => [
                'name' => $systemSettings['websiteName'],
                'url' => config('app.url'),
            ],
            'systemSettings' => $systemSettings,
            'auth' => [
                'user' => $request->user()?->only(['id', 'name', 'email', 'status']),
                'roles' => $request->user()?->getRoleNames()->values(),
                'permissions' => $request->user()?->getAllPermissions()->pluck('name')->values(),
            ],
            'demo' => app()->environment('local') && config('demo.enabled')
                ? ['enabled' => true, 'accounts' => config('demo.accounts')]
                : ['enabled' => false, 'accounts' => []],
            'flash' => [
                'success' => fn (): ?string => $request->session()->get('success'),
                'error' => fn (): ?string => $request->session()->get('error'),
            ],
        ];
    }
}
