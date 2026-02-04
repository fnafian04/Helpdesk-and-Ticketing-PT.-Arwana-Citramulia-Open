<?php

namespace App\Http\Services\Dashboard;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use Carbon\Carbon;

class HelpdeskDashboard
{
    /**
     * Get dashboard data for helpdesk
     */
    public function getDashboardData(): array
    {
        return [
            'summary' => $this->getSummary(),
            'unassigned_tickets' => $this->getUnassignedTickets(),
        ];
    }

    /**
     * Get summary data for helpdesk
     */
    private function getSummary(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $today = Carbon::now()->startOfDay();

        return [
            'this_week' => Ticket::where('created_at', '>=', $startOfWeek)->count(),
            'technicians' => User::role('technician')->where('is_active', true)->count(),
            'unassigned' => Ticket::whereDoesntHave('assignment')->count(),
            'assigned_today' => TicketAssignment::whereDate('assigned_at', $today)->count(),
        ];
    }

    /**
     * Get unassigned tickets
     */
    private function getUnassignedTickets(): array
    {
        return Ticket::whereDoesntHave('assignment')
            ->with([
                'category:id,name',
                'requester:id,name,department_id',
                'requester.department:id,name',
                'status:id,name',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'description' => $ticket->description,
                    'category' => $ticket->category ? $ticket->category->name : 'Unknown',
                    'status' => $ticket->status ? $ticket->status->name : 'Unknown',
                    'requester' => [
                        'id' => $ticket->requester->id,
                        'name' => $ticket->requester->name,
                        'department' => $ticket->requester->department ? [
                            'id' => $ticket->requester->department->id,
                            'name' => $ticket->requester->department->name
                        ] : null,
                    ],
                    'created_at' => $ticket->created_at->format('Y-m-d H:i'),
                ];
            })
            ->toArray();
    }
}
