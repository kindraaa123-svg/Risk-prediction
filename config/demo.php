<?php

return [
    'enabled' => (bool) env('DEMO_LOGIN_ENABLED', false),
    'accounts' => [
        'Superadmin' => ['email' => env('DEMO_SUPERADMIN_EMAIL', 'demo-superadmin@example.test'), 'password' => env('DEMO_SUPERADMIN_PASSWORD', 'demo-superadmin')],
        'Admin' => ['email' => env('DEMO_ADMIN_EMAIL', 'demo-admin@example.test'), 'password' => env('DEMO_ADMIN_PASSWORD', 'demo-admin')],
        'Project Manager' => ['email' => env('DEMO_PROJECT_MANAGER_EMAIL', 'demo-manager@example.test'), 'password' => env('DEMO_PROJECT_MANAGER_PASSWORD', 'demo-manager')],
        'Team Member' => ['email' => env('DEMO_TEAM_MEMBER_EMAIL', 'demo-member@example.test'), 'password' => env('DEMO_TEAM_MEMBER_PASSWORD', 'demo-member')],
        'Management' => ['email' => env('DEMO_MANAGEMENT_EMAIL', 'demo-management@example.test'), 'password' => env('DEMO_MANAGEMENT_PASSWORD', 'demo-management')],
    ],
];
