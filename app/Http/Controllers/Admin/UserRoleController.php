<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserRoleController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'is_blocked', 'created_at'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_display' => $user->getRoleDisplayName(),
                    'is_blocked' => $user->is_blocked,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('admin/UserRoles', [
            'users' => $users,
            'available_roles' => [
                'user' => 'User',
                'admin' => 'Admin',
                'admin_officer' => 'Admin Officer (Senior)'
            ]
        ]);
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:user,admin,admin_officer'
        ]);

        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        // Only Admin Officers can change user roles
        if (!$currentUser->canManageRoles()) {
            abort(403, 'Only Admin Officers can change user roles');
        }

        // Update the role
        $user->role = $request->role;
        
        // Also update is_admin for backward compatibility
        $user->is_admin = in_array($request->role, ['admin', 'admin_officer']);
        
        $user->save();

        return redirect()->back()->with('success', 'User role updated successfully');
    }
}
