<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailVerificationStatusController extends Controller
{
    /**
     * Return current user's email verification status and target redirect.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $verified = $user && $user->hasVerifiedEmail();

        $redirect = null;
        if ($verified) {
            $redirect = (method_exists($user, 'canAccessDashboard') && $user->canAccessDashboard())
                ? route('dashboard', absolute: false)
                : route('home');
        }

        return response()->json([
            'verified' => $verified,
            'redirect' => $redirect,
        ]);
    }
}
