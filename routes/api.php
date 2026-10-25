<?php

use App\Http\Controllers\Api\Admin\AdminManagementController;
use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\PetugasController;
use App\Http\Controllers\Api\Admin\SystemController;
use App\Http\Controllers\Api\Admin\VoterController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Petugas\CandidateController;
use App\Http\Controllers\Api\Petugas\EventController;
use App\Http\Controllers\Api\Petugas\MonitoringController;
use App\Http\Controllers\Api\Voter\VotingController;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [AuthController::class, 'userLogin']);
    Route::post('/auth/users/login', [AuthController::class, 'userLogin']);
    Route::post('/auth/voter/login', [AuthController::class, 'voterLogin']);
    Route::post('/auth/voters/login', [AuthController::class, 'voterLogin']);


    Route::middleware(['auth:users'])->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::middleware('role:SUPER_ADMIN')->prefix('admin')->group(function () {

            Route::get('/admins', [AdminManagementController::class, 'index']);
            Route::post('/admins', [AdminManagementController::class, 'store']);
            Route::put('/admins/{id}', [AdminManagementController::class, 'update']);
            Route::delete('/admins/{id}', [AdminManagementController::class, 'destroy']);

            Route::get('/petugas', [PetugasController::class, 'index']);
            Route::post('/petugas', [PetugasController::class, 'store']);
            Route::put('/petugas/{id}', [PetugasController::class, 'update']);
            Route::delete('/petugas/{id}', [PetugasController::class, 'destroy']);

            Route::get('/voters', [VoterController::class, 'index']);
            Route::post('/voters', [VoterController::class, 'store']);
            Route::put('/voters/{id}', [VoterController::class, 'update']);
            Route::delete('/voters/{id}', [VoterController::class, 'destroy']);
            Route::post('/voters/import', [VoterController::class, 'import']);
            Route::patch('/voters/{id}/toggle-status', [VoterController::class, 'toggleStatus']);

            Route::get('/audit-logs', [AuditLogController::class, 'index']);

            Route::get('/system/stats', [SystemController::class, 'stats']);
            Route::post('/system/events/{eventId}/reset-votes', [SystemController::class, 'resetEventVotes']);
            Route::post('/system/clear-cache', [SystemController::class, 'clearCache']);
            Route::get('/system/voters/export', [SystemController::class, 'exportVoters']);
        });

        Route::middleware('role:PETUGAS,SUPER_ADMIN')->prefix('petugas')->group(function () {

            Route::get('/events', [EventController::class, 'index']);
            Route::post('/events', [EventController::class, 'store']);
            Route::get('/events/{id}', [EventController::class, 'show']);
            Route::put('/events/{id}', [EventController::class, 'update']);
            Route::delete('/events/{id}', [EventController::class, 'destroy']);
            Route::patch('/events/{id}/status', [EventController::class, 'updateStatus']);

            Route::get('/events/{id}/invite-info', [EventController::class, 'getInviteInfo']);
            Route::post('/events/{id}/regenerate-invite-token', [EventController::class, 'regenerateInviteToken']);
            Route::get('/events/join/{token}', [EventController::class, 'getJoinEventInfo']);
            Route::post('/events/join/{token}', [EventController::class, 'joinViaInviteToken']);
            Route::post('/events/join-invite/{token}', [EventController::class, 'joinViaInviteToken']);
            Route::delete('/events/{id}/officers/{officerId}', [EventController::class, 'removeOfficer']);

            Route::post('/events/{eventId}/candidates', [CandidateController::class, 'store']);
            Route::put('/candidates/{id}', [CandidateController::class, 'update']);
            Route::delete('/candidates/{id}', [CandidateController::class, 'destroy']);

            Route::get('/events/{eventId}/monitor', [MonitoringController::class, 'monitor']);
            Route::get('/events/{eventId}/export', [MonitoringController::class, 'export']);
        });
    });

    Route::middleware(['auth:voters'])->prefix('voter')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::post('/events/join', [VotingController::class, 'join']);
        Route::get('/events/{eventId}', [VotingController::class, 'show']);
        Route::post('/events/{eventId}/vote', [VotingController::class, 'vote']);
        Route::get('/events/{eventId}/results', [VotingController::class, 'results']);
    });
});
