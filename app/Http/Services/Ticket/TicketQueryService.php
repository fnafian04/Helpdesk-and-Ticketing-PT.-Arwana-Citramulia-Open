<?php

namespace App\Http\Services\Ticket;

use App\Models\Ticket;
use App\Models\User;

class TicketQueryService
{
    /**
     * Get tickets for index endpoint dengan support filter
     * Query parameters:
     * - status: filter berdasarkan status (open, assigned, in progress, resolved, closed)
     * - category_id: filter berdasarkan category
     * - assigned_to: filter berdasarkan technician (ID)
     * - search: search berdasarkan subject atau ticket_number
     * - sort_by: sort berdasarkan field (created_at, ticket_number, subject) - default: created_at
     * - sort_order: sort order (asc, desc) - default: desc
     * - page: halaman (default: 1)
     * - per_page: jumlah item per halaman (default: 15)
     */
    public function listTickets(User $user, ?string $status = null, ?int $categoryId = null, ?int $assignedTo = null, ?string $search = null, string $sortBy = 'created_at', string $sortOrder = 'desc', int $page = 1, int $perPage = 15)
    {
        $query = Ticket::with(['status', 'category', 'requester.department', 'assignment.technician']);

        // Role-based filtering
        if ($user->hasRole('requester')) {
            $query->where('requester_id', $user->id);
        } elseif ($user->hasRole('technician')) {
            $query->whereHas('assignments', fn ($a) =>
                $a->where('technician_id', $user->id)
            );
        }

        // Filter by status
        if ($status) {
            $query->whereHas('status', fn ($q) =>
                $q->where('name', strtolower($status))
            );
        }

        // Filter by category
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Filter by assigned technician
        if ($assignedTo) {
            $query->whereHas('assignment', fn ($q) =>
                $q->where('assigned_to', $assignedTo)
            );
        }

        // Search by subject or ticket_number
        if ($search) {
            $query->where(fn ($q) =>
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
            );
        }

        // Sorting
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $total = $query->count();
        $tickets = $query->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get();

        return [
            'data' => $tickets,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
                'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'to' => min($page * $perPage, $total),
            ]
        ];
    }

    /**
     * Tickets created by requester
     */
    public function myTickets(User $user)
    {
        return Ticket::where('requester_id', $user->id)
            ->with(['status', 'category', 'requester:id,name,email,department_id', 'requester.department', 'assignment.technician:id,name,email'])
            ->latest()
            ->get();
    }

    /**
     * Ticket detail with relations
     */
    public function getTicketDetail(Ticket $ticket): Ticket
    {
        $ticket->load([
            'requester:id,name,email,department_id',
            'requester.department',
            'category:id,name',
            'status:id,name',
            'assignment.technician:id,name,email',
            'assignment.assigner:id,name,email',
            'solution',
            'technicianHistories.technician:id,name,email',
        ]);

        return $ticket;
    }

    /**
     * Ticket completion history
     */
    public function completionHistory(Ticket $ticket): Ticket
    {
        $ticket->load(['technicianHistories.technician:id,name,email']);

        return $ticket;
    }

    /**
     * Ticket logs history
     */
    public function logs(Ticket $ticket): Ticket
    {
        $ticket->load([
            'logs' => function ($q) {
                $q->with('user:id,name,email')
                  ->orderByDesc('created_at');
            }
        ]);

        return $ticket;
    }
}
