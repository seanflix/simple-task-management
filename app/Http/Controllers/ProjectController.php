<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Add a project and show its task list.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $project = Project::query()->create($validated);

        return to_route('home', ['project' => $project->id]);
    }

    /**
     * Rename a project.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $project->update(['name' => $validated['name']]);

        return back();
    }

    /**
     * Delete a project and the tasks that belong to it.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return to_route('home');
    }
}
