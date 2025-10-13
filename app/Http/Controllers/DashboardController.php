<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->is_admin) {
            Auth::logout();
            return redirect('/login');
        }
        
        // Always fetch fresh user data with role information
        $users = User::orderBy('id')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'is_blocked' => $user->is_blocked,
                'role' => $user->role,
            ];
        });
        
        return Inertia::render('admin/Dashboard', [
            'users' => $users,
        ]);
    }
}