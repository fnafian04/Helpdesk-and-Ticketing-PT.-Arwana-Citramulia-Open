<?php

namespace App\Http\Services\UserManagement;

class UserRoleService
{
    /**
     * Available roles for user creation
     */
    public function getAvailableRoles(): array
    {
        return ['requester','helpdesk', 'technician'];
    }

    /**
     * Valid roles for filtering
     */
    public function getValidRoles(): array
    {
        return ['helpdesk', 'technician', 'requester'];
    }

    /**
     * Roles for summary
     */
    public function getSummaryRoles(): array
    {
        return ['helpdesk', 'technician', 'requester', 'master-admin'];
    }
}
