<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            $user = $request->user();
            $destination = method_exists($user, 'canAccessDashboard') && $user->canAccessDashboard()
                ? route('dashboard', absolute: false)
                : route('home');
            return redirect()->intended($destination.'?verified=1');
        }

        $request->fulfill();

        $user = $request->user();
        $destination = method_exists($user, 'canAccessDashboard') && $user->canAccessDashboard()
            ? route('dashboard', absolute: false)
            : route('home');
        return redirect()->intended($destination.'?verified=1');
    }
}
