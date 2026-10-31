<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\AddressSettingsRequest;
use App\Http\Requests\BrandingSettingsRequest;
use App\Http\Requests\ContactSettingsRequest;
use App\Http\Requests\GeneralSettingsRequest;
use App\Http\Requests\SystemSettingsRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Activity;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Services\SystemSettingService;
use App\Models\SystemSetting;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SidebarPageController extends Controller
{
    public function systemMedia(Media $media)
    {
        abort_unless($media->model_type === SystemSetting::class && $media->getPath() && File::exists($media->getPath()), 404);

        return response()->file($media->getPath(), [
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    public function riskPrediction(): Response
    {
        $projects = Project::query()->with('tasks')->latest()->get();

        return Inertia::render('RiskPrediction/Index', [
            'projects' => $projects->map(function (Project $project): array {
                $openTasks = $project->tasks->where('status', '!=', 'completed')->count();
                $score = 0;
                $factors = [];
                if ($project->status === 'completed') {
                    $factors[] = 'Project sudah selesai';
                } else {
                    if ($project->due_date?->isPast()) { $score += 40; $factors[] = 'Deadline project sudah terlewati'; }
                    if ($project->progress < 50) { $score += 25; $factors[] = 'Progress masih di bawah 50%'; }
                    if ($openTasks >= 3) { $score += 20; $factors[] = $openTasks.' task belum selesai'; }
                    if (in_array($project->priority, ['high', 'critical'], true)) { $score += 15; $factors[] = 'Prioritas '.$project->priority; }
                }
                $score = min($score, 100);
                $level = $score >= 70 ? 'critical' : ($score >= 45 ? 'high' : ($score >= 20 ? 'medium' : 'low'));

                return ['id' => $project->id, 'name' => $project->name, 'status' => $project->status, 'progress' => $project->progress, 'due_date' => $project->due_date?->toDateString(), 'priority' => $project->priority, 'open_tasks' => $openTasks, 'score' => $score, 'level' => $level, 'factors' => $factors ?: ['Tidak ada indikator risiko aktif']];
            }),
        ]);
    }

    public function riskMitigation(): Response
    {
        return Inertia::render('RiskMitigation/Index', [
            'projects' => Project::query()->latest()->get(['id', 'name', 'status', 'progress', 'due_date']),
        ]);
    }

    public function reports(): Response
    {
        return Inertia::render('Reports/Index', [
            'summary' => [
                'projects' => Project::query()->count(),
                'active_projects' => Project::query()->where('status', 'active')->count(),
                'completed_projects' => Project::query()->where('status', 'completed')->count(),
                'tasks' => Task::query()->count(),
                'completed_tasks' => Task::query()->where('status', 'completed')->count(),
            ],
            'projects' => Project::query()->with('manager')->withCount('tasks')->orderBy('name')->get()->map(fn (Project $project): array => [
                'id' => $project->id,
                'name' => $project->name,
                'code' => $project->code,
                'manager' => $project->manager?->name,
                'status' => $project->status,
                'priority' => $project->priority,
                'progress' => $project->progress,
                'due_date' => $project->due_date?->toDateString(),
                'tasks_count' => $project->tasks_count,
            ]),
            'tasks' => Task::query()->with('project')->latest()->get()->map(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'project' => $task->project?->name,
                'status' => $task->status,
                'priority' => $task->priority,
                'progress' => $task->progress,
                'due_date' => $task->due_date?->toDateString(),
            ]),
        ]);
    }

    public function exportReportsCsv(): StreamedResponse
    {
        $filename = 'laporan-proyek-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Jenis', 'Nama', 'Status', 'Prioritas', 'Progres', 'Tenggat'], ';');

            Project::query()->orderBy('name')->each(function (Project $project) use ($handle): void {
                fputcsv($handle, [
                    'Proyek',
                    $project->name,
                    $project->status,
                    $project->priority,
                    $project->progress.'%',
                    $project->due_date?->toDateString() ?? '',
                ], ';');
            });

            Task::query()->with('project')->orderBy('title')->each(function (Task $task) use ($handle): void {
                fputcsv($handle, [
                    'Task',
                    ($task->project?->name ? $task->project->name.' - ' : '').$task->title,
                    $task->status,
                    $task->priority,
                    $task->progress.'%',
                    $task->due_date?->toDateString() ?? '',
                ], ';');
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function activityLogs(Request $request): Response
    {
        $search = trim($request->string('search')->toString());
        $logName = trim($request->string('log_name')->toString());
        $dateFrom = $request->date('date_from')?->startOfDay();
        $dateTo = $request->date('date_to')?->endOfDay();
        $query = Activity::query()->with('causer')->latest();

        $query->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
            $query->where('description', 'like', '%'.$search.'%')
                ->orWhere('ip_address', 'like', '%'.$search.'%')
                ->orWhereHas('causer', fn ($causer) => $causer->where('name', 'like', '%'.$search.'%'));
        }));
        $query->when($logName, fn ($query) => $query->where('log_name', $logName));
        $query->when($dateFrom, fn ($query) => $query->where('created_at', '>=', $dateFrom));
        $query->when($dateTo, fn ($query) => $query->where('created_at', '<=', $dateTo));

        return Inertia::render('Admin/ActivityLogs/Index', [
            'activities' => $query->limit(50)->get()->map(fn (Activity $activity): array => [
                'id' => $activity->id,
                'description' => $activity->description,
                'log_name' => $activity->log_name,
                'causer' => $activity->causer?->name ?? 'System',
                'subject' => $activity->subject_type ? class_basename($activity->subject_type).' #'.$activity->subject_id : '—',
                'ip_address' => $activity->ip_address ?? '—',
                'created_at' => $activity->created_at?->toISOString(),
            ]),
            'logNames' => Activity::query()->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name')->values(),
            'filters' => ['search' => $search, 'log_name' => $logName, 'date_from' => $request->input('date_from', ''), 'date_to' => $request->input('date_to', '')],
        ]);
    }

    public function databaseBackups(): Response
    {
        $files = collect(Storage::disk('local')->files('backups'))
            ->filter(fn (string $path): bool => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['sql', 'sqlite', 'gz', 'zip'], true))
            ->sortByDesc(fn (string $path): int => Storage::disk('local')->lastModified($path))
            ->values()
            ->map(fn (string $path): array => [
                'name' => basename($path),
                'size' => Storage::disk('local')->size($path),
                'modified_at' => date(DATE_ATOM, Storage::disk('local')->lastModified($path)),
            ]);

        return Inertia::render('Admin/Backups/Index', [
            'backups' => $files,
            'backup_directory' => 'storage/app/private/backups',
        ]);
    }

    public function createDatabaseBackup(): RedirectResponse
    {
        abort_unless(config('database.default') === 'sqlite', 422, 'Backup otomatis saat ini hanya tersedia untuk database SQLite.');
        $databasePath = config('database.connections.sqlite.database');
        $isAbsolutePath = is_string($databasePath) && preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $databasePath) === 1;
        $databasePath = $databasePath === ':memory:' ? null : ($isAbsolutePath ? $databasePath : base_path((string) $databasePath));
        abort_unless($databasePath && File::exists($databasePath), 422, 'File database SQLite tidak ditemukan.');

        Storage::disk('local')->makeDirectory('backups');
        $filename = 'database-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(6)).'.sqlite';
        $target = Storage::disk('local')->path('backups/'.$filename);
        File::copy($databasePath, $target);

        activity('backups')->causedBy(auth()->user())->withProperties(['filename' => $filename, 'size' => File::size($target)])->log('Database backup created');

        return back()->with('success', 'Database backup berhasil dibuat: '.$filename);
    }

    public function downloadDatabaseBackup(string $filename): BinaryFileResponse
    {
        abort_unless($filename === basename($filename), 404);

        $path = Storage::disk('local')->path('backups/'.$filename);
        abort_unless(File::isFile($path), 404, 'File backup tidak ditemukan.');

        return response()->download($path, $filename);
    }

    public function systemSettings(SystemSettingService $settings): Response
    {
        return Inertia::render('Admin/SystemSettings/Index', [
            'values' => $settings->all(),
            'branding' => $settings->public(),
        ]);
    }

    public function updateGeneralSettings(GeneralSettingsRequest $request, SystemSettingService $settings): RedirectResponse
    {
        $settings->update($request->validated(), 'general');
        return back()->with('success', 'General settings updated successfully.');
    }

    public function updateAllSettings(SystemSettingsRequest $request, SystemSettingService $settings): RedirectResponse
    {
        $validated = $request->validated();
        $settings->update([
            'website_name' => $validated['website_name'],
            'contact' => $validated['contact'] ?? '',
            'company_address' => $validated['company_address'] ?? '',
        ], 'all');
        if ($request->hasFile('logo')) $settings->putMedia('website_logo', $request->file('logo'));
        if ($request->hasFile('favicon')) $settings->putMedia('website_favicon', $request->file('favicon'));

        return back()->with('success', 'System settings berhasil disimpan.');
    }

    public function updateContactSettings(ContactSettingsRequest $request, SystemSettingService $settings): RedirectResponse
    {
        $settings->update($request->validated(), 'contact');
        return back()->with('success', 'Contact information saved successfully.');
    }

    public function updateAddressSettings(AddressSettingsRequest $request, SystemSettingService $settings): RedirectResponse
    {
        $settings->update($request->validated(), 'address');
        return back()->with('success', 'Company address updated successfully.');
    }

    public function updateBrandingSettings(BrandingSettingsRequest $request, SystemSettingService $settings): RedirectResponse
    {
        if ($request->hasFile('logo')) {
            $settings->putMedia('website_logo', $request->file('logo'));
        }
        if ($request->hasFile('favicon')) {
            $settings->putMedia('website_favicon', $request->file('favicon'));
        }
        return back()->with('success', 'Website branding updated successfully.');
    }

    public function removeBranding(string $type, SystemSettingService $settings): RedirectResponse
    {
        abort_unless(in_array($type, ['logo', 'favicon'], true), 404);
        $settings->removeMedia('website_'.$type);
        return back()->with('success', ucfirst($type).' removed successfully.');
    }

    public function module(string $module): Response
    {
        $modules = [
            'projects' => ['title' => 'Projects', 'eyebrow' => 'Workspace', 'description' => 'Kelola portfolio project, anggota tim, jadwal, dan progres.'],
            'tasks' => ['title' => 'Tasks', 'eyebrow' => 'Workspace', 'description' => 'Atur pekerjaan tim, deadline, status, dan assignment.'],
            'risk-prediction' => ['title' => 'Risk prediction', 'eyebrow' => 'Risk intelligence', 'description' => 'Pantau prediksi potensi keterlambatan berdasarkan data project.'],
            'risk-mitigation' => ['title' => 'Risk mitigation', 'eyebrow' => 'Risk intelligence', 'description' => 'Rencanakan dan monitor tindakan mitigasi untuk project berisiko.'],
            'reports' => ['title' => 'Reports', 'eyebrow' => 'Insights', 'description' => 'Buat laporan project, performa, risiko, dan keterlambatan.'],
        ];

        abort_unless(isset($modules[$module]), 404);

        return Inertia::render('Modules/Index', ['module' => $modules[$module]]);
    }

    public function accessControl(): Response
    {
        return Inertia::render('Admin/AccessControl/Matrix', [
            'roles' => Role::query()->with('permissions')->orderBy('name')->get(['id', 'name']),
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function updateAccessControl(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->name === 'Superadmin', 422, 'Permission Superadmin dilindungi dan tidak dapat diubah.');

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', 'Hak akses role '.$role->name.' berhasil disimpan.');
    }

    public function notifications(Request $request): Response
    {
        return Inertia::render('Notifications/Index', [
            'notifications' => $request->user()->notifications()->latest()->limit(20)->get()->map(fn ($notification): array => [
                'id' => $notification->id,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at?->toISOString(),
                'data' => $notification->data,
            ]),
        ]);
    }

    public function profile(Request $request): Response
    {
        return Inertia::render('Profile/Edit', ['user' => $request->user()->only(['name', 'email', 'phone', 'status', 'created_at'])]);
    }

    public function updateProfile(ProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('success', 'Profile berhasil diperbarui.');
    }

    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        if (! Hash::check($request->string('current_password'), $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'Password lama tidak sesuai.']);
        }

        $request->user()->forceFill(['password' => $request->string('password')->toString()])->save();

        return back()->with('success', 'Password berhasil diganti.');
    }
}
