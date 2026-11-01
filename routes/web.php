<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventStaffController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserManagementController;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('events.index');

    // Student QR
    Route::get('/my-qr', fn () => view('my-qr'))->name('my-qr');
    Route::get('/my-qr/code', [AttendanceController::class, 'myCode'])->name('my-qr.code');
    Route::get('/my-attendance', [AttendanceController::class, 'history'])->name('my-attendance');

    // Scanning: the controller checks admin, creator, or assigned staff
    Route::get('/events/{event}/scan', [AttendanceController::class, 'scan'])->name('events.scan');
    Route::post('/events/{event}/scan', [AttendanceController::class, 'store'])->name('events.scan.store');
    Route::post('/events/{event}/manual', [AttendanceController::class, 'manual'])->name('events.manual');
    Route::get('/events/{event}/attendees', [AttendanceController::class, 'attendees'])->name('events.attendees');

    // Admin and organizer only
    Route::middleware('role:admin,organizer')->group(function () {
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

        Route::get('/events/{event}/staff', [EventStaffController::class, 'index'])->name('events.staff');
        Route::post('/events/{event}/staff', [EventStaffController::class, 'store'])->name('events.staff.store');
        Route::delete('/events/{event}/staff/{user}', [EventStaffController::class, 'destroy'])->name('events.staff.destroy');
        Route::get('/events/archived', [EventController::class, 'archived'])->name('events.archived');
Route::patch('/events/{event}/restore', [EventController::class, 'restore'])->withTrashed()->name('events.restore');
Route::get('/events/{event}/export', [ReportController::class, 'event'])->name('events.export');
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/users');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
});
    });
});

Route::get('/admin', function () {
    return 'Admin area';
})->middleware(['auth', 'role:admin']);

require __DIR__.'/auth.php';