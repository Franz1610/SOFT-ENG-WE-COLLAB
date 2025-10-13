<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }
        $users = User::all();
        return response()->json($users);
    }

    public function edit($id)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        return Inertia::render('EditUser', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email', 'is_admin']));
        return response()->json($user);
    }

    public function destroy($id)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->delete();
        
        // Return empty response with 200 status
        return response('', 200);
    }

    public function toggleBlock($id)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser || !$currentUser->hasAdminAccess()) {
            abort(403, 'Admin access required');
        }
        
        $targetUser = User::findOrFail($id);
        
        // Check if the target user can be blocked by the current user
        if (!$targetUser->canBeBlockedBy($currentUser)) {
            abort(403, 'You do not have permission to block this user');
        }
        
        // Toggle the blocked status
        $targetUser->is_blocked = !$targetUser->is_blocked;
        $targetUser->save();
        
        // Return redirect to dashboard with fresh data
        return redirect()->route('dashboard')->with('success', 
            $targetUser->is_blocked ? 'User has been blocked successfully.' : 'User has been unblocked successfully.'
        );
    }
}
