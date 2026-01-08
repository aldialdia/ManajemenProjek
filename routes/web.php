<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Projects
Route::resource('projects', ProjectController::class);

// Tasks
Route::get('/tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
Route::resource('tasks', TaskController::class);

// Clients
Route::resource('clients', ClientController::class);

// Users (Team Management)
Route::resource('users', UserController::class);

// Reports
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

// Profile
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

// Comments (nested under tasks)
Route::post('/tasks/{task}/comments', function (\App\Models\Task $task, \Illuminate\Http\Request $request) {
    $request->validate(['body' => 'required|string']);

    $task->comments()->create([
        'body' => $request->body,
        'user_id' => auth()->id() ?? 1, // Default to user 1 if not logged in
    ]);

    return back()->with('success', 'Comment added successfully.');
})->name('comments.store');

// Auth placeholder routes (will be replaced by Laravel Breeze/Jetstream)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');
