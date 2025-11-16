<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import { router, usePage } from '@inertiajs/vue3';

interface Props {
    active?: string;
}

withDefaults(defineProps<Props>(), {
    active: 'home',
});

const page = usePage();
const user = page.props.auth?.user;

function handleAuth() {
    if (user) {
        router.post('/logout');
    } else {
        router.visit('/login');
    }
}
</script>

<template>
        <AppShell class="flex-col">
            <AppHeader :user="user" :active="active" @auth="handleAuth" />
            <AppContent>
                <slot />
            </AppContent>
        </AppShell>
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
    border-bottom: none !important;
}
</style>
