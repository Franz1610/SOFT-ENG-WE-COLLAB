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
        
        // Always fetch fresh user data
        $users = User::orderBy('id')->get();
        
        return Inertia::render('admin/Dashboard', [
            'users' => $users,
        ]);
    }
}