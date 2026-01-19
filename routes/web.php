<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TimeTrackingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

// Auth routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', fn() => view('auth.register'))->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Auth routes (Authenticated)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Verification routes (Authenticated but not yet verified)
Route::middleware('auth')->group(function () {
    Route::get('/verify', [VerificationController::class, 'index']);
    Route::post('/verify', [VerificationController::class, 'store']);
    Route::get('/verify/{unique_id}', [VerificationController::class, 'show']);
    Route::put('/verify/{unique_id}', [VerificationController::class, 'update']);
});

// Protected routes (Authenticated and verified)
Route::middleware(['auth', 'check_status'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);

    // Documents (Module 8)
    Route::get('/projects/{project}/documents', [DocumentController::class, 'index'])->name('projects.documents.index');
    Route::get('/projects/{project}/documents/create', [DocumentController::class, 'create'])->name('projects.documents.create');
    Route::post('/projects/{project}/documents', [DocumentController::class, 'store'])->name('projects.documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/versions', [DocumentController::class, 'storeVersion'])->name('documents.add-version');
    Route::get('/document-versions/{version}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/document-versions/{version}', [DocumentController::class, 'destroyVersion'])->name('document-versions.destroy');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Tasks
    Route::get('/tasks/calendar', [TaskController::class, 'calendar'])->name('tasks.calendar');
    Route::patch('/tasks/{task}/dates', [TaskController::class, 'updateDates'])->name('tasks.update-dates');
    Route::get('/tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::resource('tasks', TaskController::class);

    // Clients
    Route::resource('clients', ClientController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Comments
    Route::post('/tasks/{task}/comments', [CommentController::class, 'storeForTask'])->name('tasks.comments.store');
    Route::post('/projects/{project}/comments', [CommentController::class, 'storeForProject'])->name('projects.comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Attachments
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'storeForTask'])->name('tasks.attachments.store');
    Route::post('/projects/{project}/attachments', [AttachmentController::class, 'storeForProject'])->name('projects.attachments.store');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Team Management
    Route::get('/projects/{project}/team', [TeamController::class, 'index'])->name('projects.team.index');
    Route::patch('/projects/{project}/team/{user}/role', [TeamController::class, 'updateRole'])->name('projects.team.updateRole');
    Route::delete('/projects/{project}/team/{user}', [TeamController::class, 'remove'])->name('projects.team.remove');
    Route::delete('/invitations/{invitation}/cancel', [TeamController::class, 'cancelInvitation'])->name('projects.team.cancelInvitation');

    // Project Invitations
    Route::post('/projects/{project}/invitations', [ProjectInvitationController::class, 'store'])->name('projects.invitations.store');
    Route::get('/invitations/{token}', [ProjectInvitationController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{token}/accept', [ProjectInvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{token}/decline', [ProjectInvitationController::class, 'decline'])->name('invitations.decline');

    // Time Tracking
    Route::get('/time-tracking', [TimeTrackingController::class, 'index'])->name('time-tracking.index');
    Route::post('/time-tracking/start', [TimeTrackingController::class, 'start'])->name('time-tracking.start');
    Route::post('/time-tracking/{timeEntry}/stop', [TimeTrackingController::class, 'stop'])->name('time-tracking.stop');
    Route::post('/time-tracking', [TimeTrackingController::class, 'store'])->name('time-tracking.store');
    Route::delete('/time-tracking/{timeEntry}', [TimeTrackingController::class, 'destroy'])->name('time-tracking.destroy');
    Route::get('/time-tracking/status', [TimeTrackingController::class, 'status'])->name('time-tracking.status');

    // API - Search users for @mentions
    Route::get('/api/users/search', [UserController::class, 'search'])->name('api.users.search');
});
