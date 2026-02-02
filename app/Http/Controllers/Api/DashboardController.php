<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\Dashboard\MasterAdminDashboard;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private MasterAdminDashboard $masterAdminDashboard;

    public function __construct(MasterAdminDashboard $masterAdminDashboard)
    {
        $this->middleware('auth:sanctum');
        $this->masterAdminDashboard = $masterAdminDashboard;
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

        return response()->json([
            'message' => 'You do not have access to this dashboard',
        ], 403);
    }
}
