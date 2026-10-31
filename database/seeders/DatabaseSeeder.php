<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
            'reports.view', 'reports.export', 'risk-predictions.view', 'risk-predictions.run',
            'risk-mitigations.manage', 'activity-logs.view', 'backups.view', 'backups.create',
            'backups.restore', 'system-settings.view', 'system-settings.update', 'roles.manage',
            'permissions.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $rolePermissions = [
            'Superadmin' => $permissions,
            'Admin' => $permissions,
            'Project Manager' => ['projects.view', 'projects.create', 'projects.update', 'tasks.view', 'tasks.create', 'tasks.update', 'reports.view', 'reports.export', 'risk-predictions.view', 'risk-predictions.run', 'risk-mitigations.manage'],
            'Team Member' => ['projects.view', 'tasks.view', 'tasks.update', 'risk-predictions.view'],
            'Management' => ['projects.view', 'reports.view', 'reports.export', 'risk-predictions.view'],
        ];

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissionNames);
        }

        $email = env('INITIAL_SUPERADMIN_EMAIL', 'admin@example.com');
        $admin = User::firstOrCreate(['email' => $email], [
            'name' => env('INITIAL_SUPERADMIN_NAME', 'System Administrator'),
            'password' => env('INITIAL_SUPERADMIN_PASSWORD', Hash::make('change-me-now')),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['Superadmin']);

        if (config('demo.enabled')) {
            foreach (config('demo.accounts') as $roleName => $account) {
                $demoUser = User::updateOrCreate(['email' => $account['email']], [
                    'name' => $roleName.' Demo',
                    'password' => $account['password'],
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                $demoUser->syncRoles([$roleName]);
            }
        }

        $this->seedWorkspaceData($admin);
    }

    private function seedWorkspaceData(User $admin): void
    {
        if (Project::query()->exists()) {
            return;
        }

        $member = User::query()->where('status', 'active')->where('id', '!=', $admin->id)->first() ?? $admin;

        $projects = [
            ['code' => 'PRJ-001', 'name' => 'Digital Transformation', 'description' => 'Modernisasi proses operasional dan dashboard manajemen.', 'status' => 'active', 'priority' => 'high', 'start_date' => now()->subDays(24), 'due_date' => now()->addDays(38), 'progress' => 62],
            ['code' => 'PRJ-002', 'name' => 'Risk Intelligence Platform', 'description' => 'Pembangunan workflow risk prediction dan mitigation.', 'status' => 'active', 'priority' => 'critical', 'start_date' => now()->subDays(12), 'due_date' => now()->addDays(21), 'progress' => 34],
            ['code' => 'PRJ-003', 'name' => 'Team Collaboration Upgrade', 'description' => 'Peningkatan kolaborasi, task tracking, dan notifikasi.', 'status' => 'planning', 'priority' => 'medium', 'start_date' => now()->addDays(7), 'due_date' => now()->addDays(68), 'progress' => 0],
            ['code' => 'PRJ-004', 'name' => 'Legacy Reporting Migration', 'description' => 'Migrasi laporan lama ke modul reports yang baru.', 'status' => 'completed', 'priority' => 'low', 'start_date' => now()->subDays(90), 'due_date' => now()->subDays(8), 'progress' => 100],
        ];

        foreach ($projects as $projectData) {
            $project = Project::query()->updateOrCreate(['code' => $projectData['code']], [...$projectData, 'manager_id' => $admin->id]);
            $tasks = match ($project->code) {
                'PRJ-001' => [
                    ['title' => 'Audit proses bisnis', 'status' => 'completed', 'priority' => 'high', 'progress' => 100, 'due_date' => now()->subDays(8)],
                    ['title' => 'Implementasi dashboard KPI', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 65, 'due_date' => now()->addDays(12)],
                    ['title' => 'User acceptance test', 'status' => 'todo', 'priority' => 'medium', 'progress' => 0, 'due_date' => now()->addDays(28)],
                ],
                'PRJ-002' => [
                    ['title' => 'Definisi indikator risiko', 'status' => 'completed', 'priority' => 'critical', 'progress' => 100, 'due_date' => now()->subDays(2)],
                    ['title' => 'Integrasi risk prediction model', 'status' => 'in_progress', 'priority' => 'critical', 'progress' => 40, 'due_date' => now()->addDays(9)],
                    ['title' => 'Review mitigation workflow', 'status' => 'review', 'priority' => 'high', 'progress' => 70, 'due_date' => now()->addDays(4)],
                ],
                'PRJ-003' => [
                    ['title' => 'Susun kebutuhan kolaborasi', 'status' => 'todo', 'priority' => 'medium', 'progress' => 0, 'due_date' => now()->addDays(18)],
                    ['title' => 'Rancang notifikasi aktivitas', 'status' => 'todo', 'priority' => 'low', 'progress' => 0, 'due_date' => now()->addDays(32)],
                ],
                default => [
                    ['title' => 'Migrasi template laporan', 'status' => 'completed', 'priority' => 'low', 'progress' => 100, 'due_date' => now()->subDays(20)],
                    ['title' => 'Validasi hasil migrasi', 'status' => 'completed', 'priority' => 'low', 'progress' => 100, 'due_date' => now()->subDays(8)],
                ],
            };

            foreach ($tasks as $taskData) {
                Task::query()->updateOrCreate(
                    ['project_id' => $project->id, 'title' => $taskData['title']],
                    [...$taskData, 'assignee_id' => $member->id, 'description' => 'Data operasional demo untuk '.$project->name.'.'],
                );
            }
        }
    }
}
