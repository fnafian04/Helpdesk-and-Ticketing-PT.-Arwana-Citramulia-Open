<?php

namespace App\Http\Services\Dashboard;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Category;
use App\Models\Department;
use App\Models\TicketStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterAdminDashboard
{
    /**
     * Get dashboard data for master admin
     */
    public function getDashboardData(): array
    {
        return [
            'summary' => $this->getSummary(),
            'ticket_trend' => $this->getTicketTrend(),
            'category_distribution' => $this->getCategoryDistribution(),
            'latest_tickets' => $this->getLatestTickets(),
        ];
    }

    /**
     * Get summary data: users, technicians, tickets this month, departments
     */
    private function getSummary(): array
    {
        $currentMonth = Carbon::now();

        return [
            'users' => User::count(),
            'technicians' => User::role('technician')->count(),
            'tickets_month' => Ticket::whereMonth('created_at', $currentMonth->month)
                ->whereYear('created_at', $currentMonth->year)
                ->count(),
            'departments' => Department::count(),
        ];
    }

    /**
     * Get ticket trend for last 7 days
     */
    private function getTicketTrend(): array
    {
        $trend = [];
        $today = Carbon::now();

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $count = Ticket::whereDate('created_at', $date->toDateString())->count();

            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'), // Monday, Tuesday, etc.
                'count' => $count,
            ];
        }

        return $trend;
    }

    /**
     * Get category distribution of tickets
     */
    private function getCategoryDistribution(): array
    {
        return Ticket::select('categories.id', 'categories.name')
            ->selectRaw('COUNT(tickets.id) as count')
            ->join('categories', 'tickets.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'count' => $category->count,
                    'percentage' => $this->calculatePercentage($category->count),
                ];
            })
            ->toArray();
    }

    /**
     * Get latest 5 tickets
     */
    private function getLatestTickets(): array
    {
        return Ticket::with([
            'requester:id,name,email',
            'status:id,name',
            'category:id,name',
            'assignment' => function ($q) {
                $q->with('technician:id,name');
            },
        ])
        ->orderByDesc('created_at')
        ->limit(5)
        ->get()
        ->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'description' => substr($ticket->description, 0, 100) . '...',
                'status' => $ticket->status ? $ticket->status->name : 'Unknown',
                'category' => $ticket->category ? $ticket->category->name : 'Unknown',
                'requester' => $ticket->requester ? (object)[
                    'id' => $ticket->requester->id,
                    'name' => $ticket->requester->name,
                    'email' => $ticket->requester->email,
                ] : null,
                'technician' => $ticket->assignment && $ticket->assignment->technician ? (object)[
                    'id' => $ticket->assignment->technician->id,
                    'name' => $ticket->assignment->technician->name,
                ] : null,
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
            ];
        })
        ->toArray();
    }

    /**
     * Calculate percentage of total tickets
     */
    private function calculatePercentage($count): float
    {
        $total = Ticket::count();
        if ($total == 0) {
            return 0;
        }
        return round(($count / $total) * 100, 2);
    }
}
