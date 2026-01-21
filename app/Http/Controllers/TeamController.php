<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    /**
     * Display team management page.
     */
    public function index(Project $project): View
    {
        $user = auth()->user();
        $userRole = $user->getRoleInProject($project);

        // Check if user is member of project
        if (!$userRole) {
            abort(403, 'Anda bukan anggota project ini.');
        }

        $members = $project->users()->orderByRaw("
            CASE 
                WHEN project_user.role = 'manager' THEN 1 
                WHEN project_user.role = 'admin' THEN 2 
                ELSE 3 
            END
        ")->get();

        $pendingInvitations = ProjectInvitation::where('project_id', $project->id)
            ->pending()
            ->with('user', 'inviter')
            ->get();

        // Check if current user can invite (manager or admin)
        $canInvite = in_array($userRole, ['manager', 'admin']);
        $isManager = $userRole === 'manager';

        return view('team.index', compact('project', 'members', 'pendingInvitations', 'canInvite', 'isManager', 'userRole'));
    }

    /**
     * Update member role (manager only).
     */
    public function updateRole(Request $request, Project $project, User $user): RedirectResponse
    {
        $currentUser = auth()->user();
        $currentRole = $currentUser->getRoleInProject($project);

        // Only manager can change roles
        if ($currentRole !== 'manager') {
            return back()->with('error', 'Hanya manajer yang dapat mengubah role anggota.');
        }

        // Cannot change own role
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat mengubah role Anda sendiri.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:admin,member'],
        ]);

        // Cannot set someone as manager (only 1 manager)
        $project->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', 'Role ' . $user->name . ' berhasil diubah menjadi ' . ucfirst($validated['role']));
    }

    /**
     * Remove member from project.
     */
    public function remove(Project $project, User $user): RedirectResponse
    {
        $currentUser = auth()->user();
        $currentRole = $currentUser->getRoleInProject($project);
        $targetRole = $user->getRoleInProject($project);

        // Manager can remove anyone except themselves
        // Admin can remove members only
        if ($currentRole === 'manager') {
            if ($user->id === $currentUser->id) {
                return back()->with('error', 'Anda tidak dapat menghapus diri sendiri dari project.');
            }
        } elseif ($currentRole === 'admin') {
            if ($targetRole !== 'member') {
                return back()->with('error', 'Anda hanya dapat menghapus member dari project.');
            }
        } else {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus anggota.');
        }

        $project->users()->detach($user->id);

        return back()->with('success', $user->name . ' telah dihapus dari project.');
    }

    /**
     * Show member profile.
     */
    public function showMemberProfile(Project $project, User $user)
    {
        $currentUser = auth()->user();
        $userRole = $currentUser->getRoleInProject($project);

        // Check if current user is member of project
        if (!$userRole) {
            return response()->json(['error' => 'Anda bukan anggota project ini.'], 403);
        }

        // Check if target user is member of this project
        $memberRole = $user->getRoleInProject($project);
        if (!$memberRole) {
            return response()->json(['error' => 'User bukan anggota project ini.'], 404);
        }

        // Get member statistics for this project
        $tasksInProject = $user->tasks()->where('project_id', $project->id)->count();
        $completedTasksInProject = $user->tasks()->where('project_id', $project->id)->where('status', 'done')->count();
        $pendingTasksInProject = $user->tasks()->where('project_id', $project->id)->whereIn('status', ['todo', 'in_progress'])->count();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'initials' => $user->initials,
            'role' => $memberRole,
            'joined_at' => $user->created_at->format('d M Y'),
            'stats' => [
                'total_tasks' => $tasksInProject,
                'completed_tasks' => $completedTasksInProject,
                'pending_tasks' => $pendingTasksInProject,
            ]
        ]);
    }

    /**
     * Cancel a pending invitation.
     */
    public function cancelInvitation(ProjectInvitation $invitation): RedirectResponse
    {
        $user = auth()->user();
        $userRole = $user->getRoleInProject($invitation->project);

        // Only manager/admin or the one who sent the invitation can cancel
        if (!in_array($userRole, ['manager', 'admin']) && $invitation->invited_by !== $user->id) {
            return back()->with('error', 'Anda tidak memiliki izin untuk membatalkan undangan ini.');
        }

        $invitation->delete();

        return back()->with('success', 'Undangan berhasil dibatalkan.');
    }
}
