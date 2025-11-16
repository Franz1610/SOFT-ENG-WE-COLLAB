<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Show the email verification prompt page.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            $user = $request->user();
            $destination = method_exists($user, 'canAccessDashboard') && $user->canAccessDashboard()
                ? route('dashboard', absolute: false)
                : route('home');
            return redirect()->intended($destination);
        }

        return Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
            'email' => $request->user()->email,
        ]);
    }
}
