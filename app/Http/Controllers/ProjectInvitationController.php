<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Notifications\ProjectInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectInvitationController extends Controller
{
    /**
     * Store a new invitation.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $user = auth()->user();
        $userRole = $user->getRoleInProject($project);

        // Check if user can invite (must be manager or admin)
        if (!in_array($userRole, ['manager', 'admin'])) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengundang anggota.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:admin,member'],
        ]);

        // Manager can invite admin and member, admin can only invite admin and member
        // (not manager since there's only 1 manager per project)

        // Check if email is registered
        $invitedUser = User::where('email', $validated['email'])->first();
        if (!$invitedUser) {
            return back()->with('error', 'User dengan email tersebut tidak ditemukan. Pastikan user sudah terdaftar.');
        }

        // Check if user is already a member
        if ($project->users()->where('user_id', $invitedUser->id)->exists()) {
            return back()->with('error', 'User tersebut sudah menjadi anggota project ini.');
        }

        // Check if there's a pending invitation
        $existingInvitation = ProjectInvitation::where('project_id', $project->id)
            ->where('user_id', $invitedUser->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return back()->with('error', 'User tersebut sudah memiliki undangan yang belum direspon.');
        }

        // Create invitation
        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'invited_by' => $user->id,
            'user_id' => $invitedUser->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        // Send notification
        $invitedUser->notify(new ProjectInvitationNotification($invitation));

        return back()->with('success', 'Undangan berhasil dikirim ke ' . $invitedUser->name);
    }

    /**
     * Show invitation confirmation page.
     */
    public function show(string $token): View|RedirectResponse
    {
        $invitation = ProjectInvitation::where('token', $token)
            ->with(['project', 'inviter'])
            ->first();

        if (!$invitation) {
            return redirect()->route('dashboard')->with('error', 'Undangan tidak ditemukan.');
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            return redirect()->route('dashboard')->with('error', 'Undangan sudah kadaluarsa.');
        }

        if ($invitation->status !== 'pending') {
            return redirect()->route('dashboard')->with('error', 'Undangan sudah tidak valid.');
        }

        // Ensure current user is the invited user
        if ($invitation->user_id !== auth()->id()) {
            return redirect()->route('dashboard')->with('error', 'Undangan ini bukan untuk Anda.');
        }

        return view('invitations.show', compact('invitation'));
    }

    /**
     * Accept an invitation.
     */
    public function accept(string $token): RedirectResponse
    {
        $invitation = ProjectInvitation::where('token', $token)->first();

        if (!$invitation || !$invitation->isPending() || $invitation->user_id !== auth()->id()) {
            return redirect()->route('dashboard')->with('error', 'Undangan tidak valid.');
        }

        if ($invitation->accept()) {
            return redirect()->route('projects.show', $invitation->project)
                ->with('success', 'Selamat! Anda telah bergabung ke project ' . $invitation->project->name);
        }

        return redirect()->route('dashboard')->with('error', 'Gagal menerima undangan.');
    }

    /**
     * Decline an invitation.
     */
    public function decline(string $token): RedirectResponse
    {
        $invitation = ProjectInvitation::where('token', $token)->first();

        if (!$invitation || !$invitation->isPending() || $invitation->user_id !== auth()->id()) {
            return redirect()->route('dashboard')->with('error', 'Undangan tidak valid.');
        }

        if ($invitation->decline()) {
            return redirect()->route('dashboard')->with('success', 'Undangan ditolak.');
        }

        return redirect()->route('dashboard')->with('error', 'Gagal menolak undangan.');
    }
}
