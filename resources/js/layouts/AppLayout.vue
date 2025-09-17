<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
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
