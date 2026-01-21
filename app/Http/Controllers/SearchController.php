<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search for projects, tasks, and documents.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['projects' => [], 'tasks' => [], 'documents' => []]);
        }

        $user = auth()->user();

        // Search projects that user has access to
        $projects = Project::where('name', 'like', "%{$query}%")
            ->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->withCount('tasks')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'tasks_count' => $p->tasks_count,
            ]);

        // Search tasks in user's projects
        $tasks = Task::where('title', 'like', "%{$query}%")
            ->whereHas('project.users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('project:id,name')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'project_name' => $t->project->name ?? 'No Project',
            ]);

        // Search documents in user's projects
        $documents = Document::where('title', 'like', "%{$query}%")
            ->whereHas('project.users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('project:id,name')
            ->limit(5)
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'title' => $d->title,
                'project_name' => $d->project->name ?? 'No Project',
                'type' => $d->type,
            ]);

        return response()->json([
            'projects' => $projects,
            'tasks' => $tasks,
            'documents' => $documents,
        ]);
    }
}
