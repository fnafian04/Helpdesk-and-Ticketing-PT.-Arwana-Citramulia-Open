<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Send the email verification notification.
     * Override default to use custom notification with queue.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'department_id',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // ========================
    // RELATIONS
    // ========================

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Ticket yang dibuat user
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    // Ticket yang ditangani
    public function assignedTickets()
    {
        return $this->hasMany(TicketAssignment::class, 'assigned_to');
    }

    // History penyelesaian ticket oleh technician
    public function resolvedTicketHistories()
    {
        return $this->hasMany(TechnicianTicketHistory::class, 'technician_id');
    }

    // ========================
    // SCOPES
    // ========================

    /**
     * Scope query untuk user yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query untuk user yang non-aktif
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // ========================
    // ACCESSORS & METHODS
    // ========================

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Activate user
     */
    public function activate()
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate user
     */
    public function deactivate()
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Get the active role from the current Sanctum token.
     * Token name format: "auth_token:{role}" (e.g. "auth_token:helpdesk")
     * Falls back to the user's first (or highest priority) role if no token context.
     */
    public function activeRole(): ?string
    {
        $token = $this->currentAccessToken();

        // Cek apakah token punya attribute name (PersonalAccessToken, bukan TransientToken)
        if ($token && method_exists($token, 'getAttribute') && $token->getAttribute('name') && str_contains($token->getAttribute('name'), ':')) {
            return explode(':', $token->getAttribute('name'), 2)[1];
        }

        // Fallback: return highest priority role
        $priority = ['master-admin', 'helpdesk', 'technician', 'requester'];
        foreach ($priority as $role) {
            if ($this->hasRole($role)) {
                return $role;
            }
        }

        return $this->getRoleNames()->first();
    }

    /**
     * Check if active role matches the given role name.
     */
    public function isActiveRole(string $role): bool
    {
        return $this->activeRole() === $role;
    }

}
