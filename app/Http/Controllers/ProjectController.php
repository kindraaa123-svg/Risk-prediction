<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Projects/Index', [
            'projects' => Project::query()->with('manager')->withCount('tasks')->latest()->get()->map(fn (Project $project): array => [
                'id' => $project->id,
                'name' => $project->name,
                'code' => $project->code,
                'description' => $project->description,
                'manager' => $project->manager?->name,
                'status' => $project->status,
                'priority' => $project->priority,
                'progress' => $project->progress,
                'due_date' => $project->due_date?->toDateString(),
                'tasks_count' => $project->tasks_count,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Projects/Form', ['project' => null, 'managers' => User::query()->role('Project Manager')->orderBy('name')->get(['id', 'name'])]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        Project::create($request->validated());

        return to_route('projects.index')->with('success', 'Project berhasil dibuat.');
    }

    public function show(Project $project): Response
    {
        return Inertia::render('Projects/Show', ['project' => $project->load(['manager', 'tasks.assignee'])]);
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Projects/Form', ['project' => $project, 'managers' => User::query()->role('Project Manager')->orderBy('name')->get(['id', 'name'])]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return to_route('projects.index')->with('success', 'Project berhasil diperbarui.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        abort_unless(auth()->user()?->can('projects.delete'), 403);
        $project->delete();

        return back()->with('success', 'Project berhasil dihapus.');
    }
}
