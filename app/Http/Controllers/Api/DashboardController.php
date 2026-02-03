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

        // Master admin dashboard
        if ($user->hasRole('master-admin')) {
            $data = $this->masterAdminDashboard->getDashboardData();

            return response()->json([
                'message' => 'Dashboard data retrieved successfully',
                'data' => $data,
            ]);
        }

        // Helpdesk dashboard
        if ($user->hasRole('helpdesk')) {
            $data = $this->helpdeskDashboard->getDashboardData();

            return response()->json([
                'message' => 'Dashboard data retrieved successfully',
                'data' => $data,
            ]);
        }

        // Technician dashboard
        if ($user->hasRole('technician')) {
            $technicianDashboard = new TechnicianDashboard($user);
            $data = $technicianDashboard->getDashboardData();

            return response()->json([
                'message' => 'Dashboard data retrieved successfully',
                'data' => $data,
            ]);
        }

        // Requester dashboard
        if ($user->hasRole('requester')) {
            $requesterDashboard = new RequesterDashboard($user);
            $data = $requesterDashboard->getDashboardData();

            return response()->json([
                'message' => 'Dashboard data retrieved successfully',
                'data' => $data,
            ]);
        }

        return response()->json([
            'message' => 'You do not have access to this dashboard',
        ], 403);
    }
}