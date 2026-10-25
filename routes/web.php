<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VoterManagementController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\VoterAuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\Voter\VotingController;
use App\Models\Event;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Entry Point & Redirect
|--------------------------------------------------------------------------
| System immediately redirects to the authentication portal (no public landing).
*/
Route::get('/', function () {
    if (auth()->guard('web')->check()) {
        return redirect()->route('admin.dashboard');
    }
    if (auth()->guard('voter')->check()) {
        return redirect()->route('voter.index');
    }
    return redirect()->route('login');
})->name('home');

// Quick join room by code
Route::post('/bilik/join', [VotingController::class, 'joinRoom'])->name('voter.room.join');

/*
|--------------------------------------------------------------------------
| Voter Authentication Routes (Guard: voter)
|--------------------------------------------------------------------------
*/
Route::middleware('voter.guest')->group(function () {
    Route::get('/login', [VoterAuthController::class, 'showLoginForm'])->name('login');
    Route::get('/voter/login', [VoterAuthController::class, 'showLoginForm'])->name('voter.login');
    Route::post('/login', [VoterAuthController::class, 'login'])->name('voter.login.submit');
});

Route::post('/logout', [VoterAuthController::class, 'logout'])->name('voter.logout');

/*
|--------------------------------------------------------------------------
| Voter Portal & Booth Routes (Protected by voter.auth)
|--------------------------------------------------------------------------
*/
Route::middleware('voter.auth')->group(function () {
    Route::get('/bilik', [VotingController::class, 'index'])->name('voter.index');
    Route::get('/bilik-dashboard', [VotingController::class, 'index'])->name('voter.dashboard');

    Route::get('/bilik-suara/{roomCode}', [VotingController::class, 'showBooth'])->name('voter.booth');
    Route::post('/bilik-suara/{roomCode}/vote', [VotingController::class, 'submitVote'])->name('voter.vote.submit');
    Route::post('/bilik-suara/{roomCode}', [VotingController::class, 'submitVote'])->name('voter.vote');
    Route::get('/bilik-suara/{roomCode}/sukses', [VotingController::class, 'success'])->name('voter.success');
    Route::get('/bilik-suara/{roomCode}/sudah-memilih', [VotingController::class, 'alreadyVoted'])->name('voter.voted');
});

/*
|--------------------------------------------------------------------------
| Admin & Petugas Authentication Routes (Guard: web)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    });

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

/*
|--------------------------------------------------------------------------
| Panitia & Admin Management Portal (Protected by auth:web)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web')->group(function () {
    // Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Events Management
    Route::resource('events', EventController::class);
    Route::patch('events/{event}/status', [EventController::class, 'updateStatus'])->name('events.status.update');
    Route::get('events/{event}/collaborator/join', [EventController::class, 'joinCollaborator'])->name('events.collaborator.join');
    Route::post('events/{event}/collaborator/regenerate', [EventController::class, 'regenerateInviteToken'])->name('events.invite.regenerate');

    // Nested Candidates Management
    Route::resource('events.candidates', CandidateController::class)->except(['show']);

    // Live Monitoring & Reports
    Route::get('events/{event}/monitoring', [MonitoringController::class, 'show'])->name('events.monitoring');
    Route::get('events/{event}/monitoring/live-stats', [MonitoringController::class, 'liveStats'])->name('events.live-stats');
    Route::get('events/{event}/report/pdf', [MonitoringController::class, 'downloadPdf'])->name('events.report.pdf');
    Route::get('events/{event}/report/csv', [MonitoringController::class, 'exportCsv'])->name('events.report.csv');

    /*
    |--------------------------------------------------------------------------
    | Super Admin Only Routes (Role: SUPER_ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:SUPER_ADMIN')->prefix('admin')->name('admin.')->group(function () {
        // Petugas & User Management
        Route::resource('users', UserManagementController::class)->except(['show']);

        // DPT Master Management
        Route::get('voters/template', [VoterManagementController::class, 'downloadTemplate'])->name('voters.template');
        Route::get('voters/export', [VoterManagementController::class, 'exportCsv'])->name('voters.export');
        Route::post('voters/import', [VoterManagementController::class, 'importCsv'])->name('voters.import');
        Route::patch('voters/{voter}/toggle', [VoterManagementController::class, 'toggleStatus'])->name('voters.toggle');
        Route::resource('voters', VoterManagementController::class)->except(['show']);

        // Audit Trail & Maintenance
        Route::get('logs', [AuditLogController::class, 'index'])->name('logs.index');
        Route::post('logs/prune', [AuditLogController::class, 'prune'])->name('logs.prune');
        Route::post('logs/clear-cache', [AuditLogController::class, 'clearCache'])->name('logs.clear-cache');
    });
});

