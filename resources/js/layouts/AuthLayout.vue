<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>

<style scoped>
/* Navbar styles */
.navbar,
.app-navbar {
    background-color: #FFFAE9 !important;
    color: #4b824b !important;
    border-bottom: 2px solid #4b824b !important;
}

/* Header styles */
.app-sidebar-header,
.header {
    background-color: #4b824b !important;
    color: #FFFAE9 !important;
    border-bottom: 2px solid #FFFAE9 !important;
}
</style>
