import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role: 'user' | 'admin' | 'admin_officer';
    role_display: string;
    is_admin: boolean;
    is_blocked: boolean;
    permissions?: {
        can_access_dashboard: boolean;
        can_manage_bookings: boolean;
        can_manage_rooms: boolean;
        can_access_finance: boolean;
        can_manage_users: boolean;
        can_manage_roles: boolean;
        can_block_users: boolean;
        is_admin: boolean;
        is_admin_officer: boolean;
        has_admin_access: boolean;
    };
}

export type BreadcrumbItemType = BreadcrumbItem;
