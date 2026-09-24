<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * Show tasks with priority 1 at the top.
     */
    public function index(Request $request): Response
    {
        $projects = Project::query()->orderBy('name')->orderBy('id')->get();
        $selectedProject = $projects->firstWhere('id', $request->integer('project')) ?? $projects->first();
        $selectedProjectId = $selectedProject?->id;
        $tasks = $selectedProject ? $selectedProject->tasks()->orderBy('priority')->get() : [];

        return Inertia::render('Tasks', [
            'projects' => $projects,
            'selectedProjectId' => $selectedProjectId,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Add a task at the bottom of the list.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $projectId = $request->integer('project_id');
        $project = Project::query()->findOrFail($projectId);
        $priority = ((int) $project->tasks()->max('priority')) + 1;

        $project->tasks()->create([
            'name' => $validated['name'],
            'priority' => $priority,
        ]);

        return back();
    }

    /**
     * Rename a task. Priority only changes when the list is reordered.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $task->update(['name' => $validated['name']]);

        return back();
    }

    /**
     * Delete a task and close the gap in the priority numbers.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $priority = $task->priority;
        $project = $task->project;

        $task->delete();

        Task::query()
            ->whereBelongsTo($project)
            ->where('priority', '>', $priority)
            ->decrement('priority');

        return back();
    }

    /**
     * Save a new order. The first id becomes priority 1, the next priority 2, and so on.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:tasks,id'],
        ]);

        $ids = $validated['ids'];
        $projectIds = Task::query()->whereIn('id', $ids)->pluck('project_id')->unique();

        if ($projectIds->count() !== 1) {
            throw ValidationException::withMessages([
                'ids' => 'Include every task exactly once.',
            ]);
        }

        $projectId = (int) $projectIds->first();
        $project = Project::query()->findOrFail($projectId);
        $taskCount = $project->tasks()->count();

        if (count($ids) !== $taskCount) {
            throw ValidationException::withMessages([
                'ids' => 'Include every task exactly once.',
            ]);
        }

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $index => $id) {
                $priority = $index + 1;

                Task::query()->whereKey($id)->update([
                    'priority' => $priority,
                ]);
            }
        });

        return back();
    }
}
