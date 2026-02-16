<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminPasswordResetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

// ============================================================================
// MASTER ADMIN PASSWORD RESET (Public - tanpa auth)
// ============================================================================
Route::prefix('admin')->group(function () {
    Route::post('/forgot-password', [AdminPasswordResetController::class, 'requestOtp'])
        ->middleware('throttle:5,1'); // Max 5 request per menit
    Route::post('/verify-otp', [AdminPasswordResetController::class, 'verifyOtp'])
        ->middleware('throttle:10,1'); // Max 10 request per menit
    Route::post('/reset-password', [AdminPasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:10,1'); // Max 10 request per menit
});

// Endpoint yang hanya perlu auth (tanpa verifikasi email)
// Agar user yang belum verifikasi tetap bisa validate token, lihat profil, dan logout
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1');
    Route::get('/email/verification-status', [EmailVerificationController::class, 'status']);

    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/validate-token', [AuthController::class, 'validateToken']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/switch-role', [AuthController::class, 'switchRole']);
});

// Endpoint yang memerlukan email terverifikasi
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

// ============================================================================
// DASHBOARD ROUTES
// ============================================================================
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('active_role:master-admin|helpdesk|technician|requester');
});

// ============================================================================
// TICKET MANAGEMENT ROUTES
// ============================================================================
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Ticket Count (for badge and analytics)
    Route::get('/tickets/count', [TicketController::class, 'count'])
        ->middleware('active_permission:ticket.view');
    
    // Create & View Tickets
    Route::post('/tickets', [TicketController::class, 'store'])
        ->middleware('active_permission:ticket.create');
    
    Route::get('/tickets', [TicketController::class, 'index'])
        ->middleware('active_permission:ticket.view');
    
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
        ->middleware('active_permission:ticket.view');
    
    Route::get('/my-tickets', [TicketController::class, 'myTickets']);
    
    Route::get('/tickets/{ticket}/completion-history', [TicketController::class, 'completionHistory'])
        ->middleware('active_permission:ticket.view');

    Route::get('/tickets/{ticket}/logs', [TicketController::class, 'logs']);

    Route::get('/technician/tickets', [TicketController::class, 'technicianTickets'])
        ->middleware('active_role:technician');
    
    Route::get('/technician/completed-tickets', [TicketController::class, 'technicianCompletedTickets'])
        ->middleware('active_role:technician');

    // Ticket Assignment (Helpdesk)
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])
        ->middleware('active_permission:ticket.assign');

    // Technician Actions
    Route::post('/tickets/{ticket}/confirm', [TicketController::class, 'confirm'])
        ->middleware('active_permission:ticket.change_status');
    
    Route::post('/tickets/{ticket}/reject', [TicketController::class, 'reject'])
        ->middleware('active_permission:ticket.change_status');
    
    Route::post('/tickets/{ticket}/solve', [TicketController::class, 'solve'])
        ->middleware('active_permission:ticket.resolve');

    // Helpdesk Actions
    Route::post('/tickets/{ticket}/unresolve', [TicketController::class, 'unresolve'])
        ->middleware('active_permission:ticket.assign');
    
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])
        ->middleware('active_role:helpdesk|requester');
});

// ============================================================================
// USER MANAGEMENT ROUTES
// ============================================================================
// GET Endpoints (Master Admin + Helpdesk)
Route::middleware(['auth:sanctum', 'verified', 'active_permission:user.view'])->group(function () {
    Route::get('/users/by-role/{roleName}', [UserManagementController::class, 'getUsersByRole']);
    Route::get('/users/{user}', [UserManagementController::class, 'show'])
        ->whereNumber('user');
    Route::get('/technicians/active', [UserManagementController::class, 'getActiveTechnicians']);
});

// GET Resolved Tickets (Master Admin + Helpdesk + Technician viewing their own)
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/users/{user}/resolved-tickets', [UserManagementController::class, 'resolvedTickets'])
        ->whereNumber('user');

    Route::get('/users/{user}/assigned-tickets', [UserManagementController::class, 'assignedTickets'])
        ->whereNumber('user');
});

// POST/PUT/DELETE Endpoints (Master Admin Only)
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/roles-summary', [UserManagementController::class, 'getRolesSummary'])
            ->middleware('active_permission:user.view.all');
        
        Route::get('/', [UserManagementController::class, 'index'])
            ->middleware('active_permission:user.view.all');
        
        Route::get('/available-roles', [UserManagementController::class, 'getAvailableRoles'])
            ->middleware('active_permission:user.view.all');
        
        Route::post('/', [UserManagementController::class, 'store'])
            ->middleware('active_permission:user.create');
        
        Route::put('/{user}', [UserManagementController::class, 'update'])
            ->middleware('active_permission:user.update');

        Route::patch('/{user}/status', [UserManagementController::class, 'updateStatus'])
            ->middleware('active_permission:user.update');
        
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
            ->middleware('active_permission:user.create');
    });
});

// ============================================================================
// DEPARTMENT MANAGEMENT ROUTES
// ============================================================================
// Public GET Endpoints
Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/departments/{department}', [DepartmentController::class, 'show']);

// Protected POST/PUT/PATCH/DELETE Endpoints (Master Admin & Helpdesk)
Route::middleware(['auth:sanctum', 'verified', 'active_role:master-admin|helpdesk'])->group(function () {
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::put('/departments/{department}', [DepartmentController::class, 'update']);
    Route::patch('/departments/{department}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);
});

// ============================================================================
// CATEGORY MANAGEMENT ROUTES
// ============================================================================
// Public GET Endpoints
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Protected POST/PUT/PATCH/DELETE Endpoints (Master Admin & Helpdesk)
Route::middleware(['auth:sanctum', 'verified', 'active_role:master-admin|helpdesk'])->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::patch('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

// ============================================================================
// EXPORT ROUTES
// ============================================================================
// Export ticket report ke Excel (Master Admin & Helpdesk only)
Route::middleware(['auth:sanctum', 'verified', 'active_role:master-admin|helpdesk'])->group(function () {
    Route::get('/export', [ExportController::class, 'export']);
});