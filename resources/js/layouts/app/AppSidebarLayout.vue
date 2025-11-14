<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
  <AppLayout>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
                        <AppSidebarHeader :breadcrumbs="breadcrumbs" class="app-sidebar-header" />
            <slot />
        </AppContent>
    </AppShell>
  </AppLayout>
</template>

<style scoped>
/* Set the sidebar and content background to #FFFAE9 */
:deep(.sidebar),
:deep(.app-sidebar),
:deep(.app-content),
:deep(.app-shell) {
    background-color: #FFFAE9 !important;
    color: #344C34 !important;
}

/* Optional: update header background if needed */
:deep(.app-sidebar-header) {
    background-color: #4b824b !important;
    color: #FFFAE9 !important;
    border-bottom: none !important; /* remove white/tan line under header */
}

/* Navbar styles */
.navbar,
.app-navbar {
    background-color: #FFFAE9 !important;
    color: #4b824b !important;
    border-bottom: 2px solid #4b824b !important;
}

/* Header styles (keep consistent if other headers appear) */
.header {
    background-color: #4b824b !important;
    color: #FFFAE9 !important;
    border-bottom: none !important;
}
</style>
