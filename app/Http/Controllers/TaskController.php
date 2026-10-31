<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tasks/Index', [
            'tasks' => Task::query()->with(['project', 'assignee'])->latest()->get()->map(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'project' => $task->project?->name,
                'assignee' => $task->assignee?->name,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date?->toDateString(),
            ]),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'assignees' => User::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(TaskRequest $request): RedirectResponse
    {
        Task::create($request->validated());

        return back()->with('success', 'Task berhasil dibuat.');
    }
}
