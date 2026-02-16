<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\Dashboard\MasterAdminDashboard;
use App\Http\Services\Dashboard\HelpdeskDashboard;
use App\Http\Services\Dashboard\TechnicianDashboard;
use App\Http\Services\Dashboard\RequesterDashboard;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private MasterAdminDashboard $masterAdminDashboard;
    private HelpdeskDashboard $helpdeskDashboard;

    public function __construct(
        MasterAdminDashboard $masterAdminDashboard,
        HelpdeskDashboard $helpdeskDashboard
    ) {
        $this->middleware('auth:sanctum');
        $this->masterAdminDashboard = $masterAdminDashboard;
        $this->helpdeskDashboard = $helpdeskDashboard;
    }

    /**
     * Get dashboard data based on user role
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Dashboard berdasarkan active role yang dipilih saat login
        $activeRole = $user->activeRole();
        $dashboardData = [];

        switch ($activeRole) {
            case 'master-admin':
                $dashboardData = $this->masterAdminDashboard->getDashboardData();
                break;
            case 'helpdesk':
                $dashboardData = $this->helpdeskDashboard->getDashboardData();
                break;
            case 'technician':
                $technicianDashboard = new TechnicianDashboard($user);
                $dashboardData = $technicianDashboard->getDashboardData();
                break;
            case 'requester':
                $requesterDashboard = new RequesterDashboard($user);
                $dashboardData = $requesterDashboard->getDashboardData();
                break;
            default:
                return response()->json([
                    'message' => 'You do not have access to this dashboard',
                ], 403);
        }

        return response()->json([
            'message' => 'Dashboard data retrieved successfully',
            'data' => $dashboardData,
            'active_role' => $activeRole,
            'all_roles' => $user->getRoleNames(),
        ]);
    }
}