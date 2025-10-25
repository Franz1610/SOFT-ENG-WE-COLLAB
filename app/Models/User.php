<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_blocked',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    // Role-based permission methods
    
    /**
     * Check if user is an admin (base admin)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is an admin officer
     */
    public function isAdminOfficer(): bool
    {
        return $this->role === 'admin_officer';
    }

    /**
     * Check if user has any admin privileges
     */
    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['admin', 'admin_officer']);
    }

    /**
     * Check if user can access dashboard
     */
    public function canAccessDashboard(): bool
    {
        return $this->hasAdminAccess();
    }

    /**
     * Check if user can manage bookings
     */
    public function canManageBookings(): bool
    {
        return $this->hasAdminAccess();
    }

    /**
     * Check if user can manage rooms
     */
    public function canManageRooms(): bool
    {
        return $this->hasAdminAccess();
    }

    /**
     * Check if user can access finance management
     */
    public function canAccessFinance(): bool
    {
        return $this->role === 'admin_officer';
    }

    /**
     * Check if user can block other users
     */
    public function canBlockUsers(): bool
    {
        // Admin Officers (highest level) and base Admins can block users
        return in_array($this->role, ['admin', 'admin_officer']);
    }

    /**
     * Check if user can be blocked by another user
     */
    public function canBeBlockedBy(User $user): bool
    {
        // Admin officers (highest level) cannot be blocked by anyone
        if ($this->isAdminOfficer()) {
            return false;
        }
        
        // Base admins can only be blocked by admin officers
        if ($this->isAdmin() && !$user->isAdminOfficer()) {
            return false;
        }
        
        // Admin officers and base admins can block regular users
        if ($user->hasAdminAccess() && $this->role === 'user') {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can manage other users (view, edit, delete)
     */
    public function canManageUsers(): bool
    {
        // Both Admin and Admin Officer can manage users, but Admin Officer is higher level
        return in_array($this->role, ['admin', 'admin_officer']);
    }

    /**
     * Check if user can change roles of other users
     */
    public function canManageRoles(): bool
    {
        // Only Admin Officers can manage roles (not base admins)
        return $this->isAdminOfficer();
    }

    /**
     * Check if user can manage a specific other user
     */
    public function canManageUser(User $targetUser): bool
    {
        // Admin Officers can manage everyone
        if ($this->isAdminOfficer()) {
            return true;
        }
        
        // Base Admins can manage regular users but not Admin Officers
        if ($this->isAdmin() && !$targetUser->isAdminOfficer()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get user role display name
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'admin' => 'Admin',
            'admin_officer' => 'Admin Officer (Senior)',
            'user' => 'User',
            default => 'Unknown'
        };
    }
}
