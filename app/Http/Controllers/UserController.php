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
        return response()->json(['success' => true]);
    }
}
