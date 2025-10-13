<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $permission = null)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Handle different permission types
        switch ($permission) {
            case 'admin':
                if (!$user->hasAdminAccess()) {
                    abort(403, 'Admin access required');
                }
                break;

            case 'admin_only':
                if (!$user->isAdmin()) {
                    abort(403, 'Base admin access required');
                }
                break;

            case 'admin_officer':
                if (!$user->isAdminOfficer()) {
                    abort(403, 'Admin Officer access required');
                }
                break;

            case 'finance':
                if (!$user->canAccessFinance()) {
                    abort(403, 'Finance access restricted to Admin Officers');
                }
                break;

            case 'dashboard':
                if (!$user->canAccessDashboard()) {
                    abort(403, 'Dashboard access denied');
                }
                break;

            case 'bookings':
                if (!$user->canManageBookings()) {
                    abort(403, 'Booking management access denied');
                }
                break;

            case 'rooms':
                if (!$user->canManageRooms()) {
                    abort(403, 'Room management access denied');
                }
                break;

            case 'user_management':
                if (!$user->canManageUsers()) {
                    abort(403, 'User management access denied');
                }
                break;

            case 'role_management':
                if (!$user->canManageRoles()) {
                    abort(403, 'Role management access denied');
                }
                break;

            default:
                // If specific role is provided, check exact match
                if ($permission && $user->role !== $permission) {
                    abort(403, 'Insufficient permissions');
                }
        }

        return $next($request);
    }
}