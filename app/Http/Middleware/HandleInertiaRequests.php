<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified_at' => $request->user()->email_verified_at,
                    'contact' => $request->user()->contact,
                    'role' => $request->user()->role,
                    'role_display' => $request->user()->getRoleDisplayName(),
                    'is_admin' => $request->user()->is_admin,
                    'is_blocked' => $request->user()->is_blocked,
                    'permissions' => [
                        'can_access_dashboard' => $request->user()->canAccessDashboard(),
                        'can_manage_bookings' => $request->user()->canManageBookings(),
                        'can_manage_rooms' => $request->user()->canManageRooms(),
                        'can_access_finance' => $request->user()->canAccessFinance(),
                        'can_manage_users' => $request->user()->canManageUsers(),
                        'can_manage_roles' => $request->user()->canManageRoles(),
                        'can_block_users' => $request->user()->canBlockUsers(),
                        'is_admin' => $request->user()->isAdmin(),
                        'is_admin_officer' => $request->user()->isAdminOfficer(),
                        'has_admin_access' => $request->user()->hasAdminAccess(),
                    ]
                ] : null,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'csrf_token' => csrf_token(),
        ];
    }
}
