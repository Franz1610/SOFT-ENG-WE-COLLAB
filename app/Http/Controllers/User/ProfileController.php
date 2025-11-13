<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's own profile page (user-only version).
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('user/Profile', [
            'mustVerifyEmail' => $request->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information (first/last name and contact only).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $first = trim($validated['first_name'] ?? '');
        $last = trim($validated['last_name'] ?? '');

        $user = $request->user();
        // Store combined full name in the existing `name` column
        $user->name = trim($first . ' ' . $last);
        $user->contact = $validated['contact'] ?? $user->contact;
        $user->save();

        return to_route('user.profile.edit');
    }
}
