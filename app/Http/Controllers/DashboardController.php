<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $projects = Project::query()
            ->withCount(['tasks as open_tasks_count' => fn (Builder $query) => $query->where('status', '!=', 'completed')])
            ->get();

        $highRiskProjects = $projects->filter(function (Project $project): bool {
            if ($project->status === 'completed') {
                return false;
            }

            $score = 0;
            $score += $project->due_date?->isPast() ? 40 : 0;
            $score += $project->progress < 50 ? 25 : 0;
            $score += $project->open_tasks_count >= 3 ? 20 : 0;
            $score += in_array($project->priority, ['high', 'critical'], true) ? 15 : 0;

            return min($score, 100) >= 45;
        })->count();

        return Inertia::render('Dashboard/Index', [
            'metrics' => [
                'users' => User::query()->count(),
                'projects' => $projects->count(),
                'highRiskProjects' => $highRiskProjects,
                'activities' => Activity::query()->count(),
                'projectStatus' => $projects->groupBy('status')->map(fn ($items, $status): array => ['status' => $status, 'total' => $items->count()])->values()->all(),
                'taskStatus' => Task::query()->get(['status'])->groupBy('status')->map(fn ($items, $status): array => ['status' => $status, 'total' => $items->count()])->values()->all(),
            ],
        ]);
    }
}
