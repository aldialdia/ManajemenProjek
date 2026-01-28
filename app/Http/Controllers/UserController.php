<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Search users for @mention autocomplete.
     * If project_id is provided, only search within project members.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $projectId = $request->get('project_id');

        $userQuery = User::where('id', '!=', auth()->id());

        // If project_id is provided, filter to only project members
        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $memberIds = $project->users()->pluck('users.id')->toArray();
                $userQuery->whereIn('id', $memberIds);
            }
        }

        $users = $userQuery
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('email', 'like', '%' . $query . '%');
            })
            ->take(10)
            ->get(['id', 'name', 'email'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'initials' => $user->initials,
                ];
            });

        return response()->json($users);
    }
}
