<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\NewComment;
use App\Notifications\UserMentioned;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Store a comment for a task.
     */
    public function storeForTask(Request $request, Task $task): RedirectResponse
    {
        $request->validate(['body' => 'required|string|max:5000']);

        $comment = $task->comments()->create([
            'body' => $request->body,
            'user_id' => auth()->id(),
        ]);

        // Notify task assignee about new comment
        $this->notifyAboutComment($comment, 'task', $task->title, $task->id, $task->assignee);

        // Notify mentioned users
        $this->notifyMentionedUsers($comment, 'task', $task->title, $task->id);

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    /**
     * Store a comment for a project.
     */
    public function storeForProject(Request $request, Project $project): RedirectResponse
    {
        $request->validate(['body' => 'required|string|max:5000']);

        $comment = $project->comments()->create([
            'body' => $request->body,
            'user_id' => auth()->id(),
        ]);

        // Notify project team members about new comment
        $this->notifyProjectTeam($comment, $project);

        // Notify mentioned users
        $this->notifyMentionedUsers($comment, 'project', $project->name, $project->id);

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        // Only allow the author or admin to delete
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus komentar ini.');
        }

        $comment->delete();

        return back()->with('success', 'Komentar berhasil dihapus.');
    }

    /**
     * Notify user about new comment on task.
     */
    protected function notifyAboutComment(Comment $comment, string $type, string $title, int $id, $recipient): void
    {
        if (!$recipient) {
            return;
        }

        // Don't notify if user comments on their own task
        if ($recipient->id === auth()->id()) {
            return;
        }

        $comment->load('user');
        $recipient->notify(new NewComment($comment, $type, $title, $id));
    }

    /**
     * Notify project team about new comment.
     */
    protected function notifyProjectTeam(Comment $comment, Project $project): void
    {
        $comment->load('user');

        // Get project managers
        $managers = $project->users()
            ->wherePivot('role', 'manager')
            ->get();

        foreach ($managers as $manager) {
            // Don't notify if user comments on their own project
            if ($manager->id !== auth()->id()) {
                $manager->notify(new NewComment($comment, 'project', $project->name, $project->id));
            }
        }
    }

    /**
     * Parse @mentions from comment body and notify mentioned users.
     */
    protected function notifyMentionedUsers(Comment $comment, string $targetType, string $targetTitle, int $targetId): void
    {
        // Find all @mentions in the comment body
        // Pattern: @[User Name](user_id)
        preg_match_all('/@\[([^\]]+)\]\((\d+)\)/', $comment->body, $matches);

        if (empty($matches[2])) {
            return;
        }

        $comment->load('user');
        $mentionedUserIds = array_unique($matches[2]);
        $notifiedUserIds = [];

        foreach ($mentionedUserIds as $userId) {
            // Skip if already notified or is the commenter
            if (in_array($userId, $notifiedUserIds) || $userId == auth()->id()) {
                continue;
            }

            $user = User::find($userId);
            if ($user) {
                $user->notify(new UserMentioned(
                    $comment,
                    auth()->user(),
                    $targetType,
                    $targetTitle,
                    $targetId
                ));
                $notifiedUserIds[] = $userId;
            }
        }
    }
}
