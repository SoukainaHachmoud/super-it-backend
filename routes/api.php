<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SwitchController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AlerteController;

/*
|--------------------------------------------------------------------------
| Public Routes (Accessibles sans token)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/employers/search', [EmployeController::class, 'search']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Sécurisées par Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    Route::post('/employes', [EmployeController::class, 'store']);
    Route::put('/employes/{id}', [EmployeController::class, 'update']);
    Route::delete('/employes/{id}', [EmployeController::class, 'destroy']);

    // Le CRUD complet  (Gère GET, POST, PUT, DELETE automatiquement)
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('switches', SwitchController::class);

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets',                       [TicketController::class, 'store']);
    Route::get('/agents/{employe_id}/tickets',    [TicketController::class, 'getAgentTickets']);
    Route::get('/tickets/{id}',                   [TicketController::class, 'show']);
    Route::put('/tickets/{id}/statut',            [TicketController::class, 'updateStatut']);
    Route::put('/tickets/{id}',                   [TicketController::class, 'update']);
    Route::delete('/tickets/{id}',                [TicketController::class, 'destroy']);
    Route::get('/tickets/{id}/messages', [TicketController::class, 'getMessages']);
    Route::post('/tickets/{id}/messages', [TicketController::class, 'sendMessage']);
    Route::get('/tickets/{id}/historique', [TicketController::class, 'getHistory']);

    Route::get('/agent/tickets/notifications', [TicketController::class, 'getAgentNotifications']);
    Route::post('/tickets/{id}/read', [TicketController::class, 'markAsRead']);

    Route::post('/tickets/{id}/read-admin', [TicketController::class, 'markAsReadAdmin']);
    Route::get('/admin/tickets/notifications', [TicketController::class, 'getAdminNotifications']);

    Route::get('/admin/profile', [EmployeController::class, 'getAdminProfile']);
    Route::put('/admin/profile', [EmployeController::class, 'updateAdminProfile']);
    Route::post('/admin/update-password', [EmployeController::class, 'updateAdminPassword']);

    // Profil agent
    Route::get('/employes/{id}',             [EmployeController::class, 'show']);
    Route::post('/user/update-password',     [AuthController::class, 'updatePassword']);
    Route::put('/employes/{id}', [EmployeController::class, 'update']);

    Route::get('/alertes/active', [AlerteController::class, 'getActive']);
    Route::post('/alertes', [AlerteController::class, 'store']);
    Route::post('/alertes/desactiver', [AlerteController::class, 'desactiver']);
    Route::get('/alertes', [AlerteController::class, 'index']);

    Route::put('/employes/{id}/update-port', [SwitchController::class, 'updateAgentPort']);

     Route::put('/employes/{id}/role', [EmployeController::class, 'updateRole']);
});
