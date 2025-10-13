<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Folder, LayoutGrid, Calendar, Building, UserCheck } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const mainNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [];
    
    if (!user.value) return items;

    // Dashboard - available to all admin users
    if (user.value.permissions?.has_admin_access) {
        items.push({
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        });
    }

    // Manage Booking - available to admin and admin officer
    if (user.value.permissions?.can_manage_bookings) {
        items.push({
            title: 'Manage Booking',
            href: '/admin/bookings',
            icon: Calendar,
        });
    }

    // Room Management - available to admin and admin officer
    if (user.value.permissions?.can_manage_rooms) {
        items.push({
            title: 'Room Management',
            href: '/admin/rooms',
            icon: Building,
        });
    }

    // Finance Management - only available to admin officer
    if (user.value.permissions?.can_access_finance) {
        items.push({
            title: 'Finance Management',
            href: '/admin/finance',
            icon: Folder,
        });
    }

    // Role Management - only available to admin officer (not base admin)
    if (user.value.permissions?.is_admin_officer) {
        items.push({
            title: 'Role Management',
            href: '/admin/user-roles',
            icon: UserCheck,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [

];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

<style scoped>
.navbar,
.app-navbar {
    background-color: #FFFAE9 !important;
    color: #4b824b !important;
    border-bottom: 2px solid #4b824b !important;
}
.app-sidebar-header,
.header {
    background-color: #4b824b !important;
    color: #FFFAE9 !important;
    border-bottom: 2px solid #FFFAE9 !important;
}

</style>
