<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    hideSidebar?: boolean;
}

withDefaults(defineProps<Props>(), {
    hideSidebar: false,
});

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
    },
    {
        title: 'Password',
        href: '/settings/password',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },
];

const page = usePage();

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';

const isAdmin = computed(() => {
    const user: any = (page.props as any).auth?.user;
    const role = (user?.role as any) ?? '';
    return ['admin', 'admin_officer'].includes(role);
});

function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/');
    }
}
</script>

<template>
    <div class="settings-container">
        <div class="settings-header">
            <div class="header-row">
                <button v-if="isAdmin" class="back-btn" type="button" @click="goBack">Back</button>
                <Heading title="Settings" description="Manage your profile and account settings" />
            </div>
        </div>

        <div class="settings-content">
            <template v-if="!$props.hideSidebar">
                <aside class="settings-sidebar">
                    <nav class="sidebar-nav">
                        <Button
                            v-for="item in sidebarNavItems"
                            :key="item.href"
                            variant="ghost"
                            :class="['nav-button', { 'nav-active': currentPath === item.href }]"
                            as-child
                        >
                            <Link :href="item.href">
                                {{ item.title }}
                            </Link>
                        </Button>
                    </nav>
                </aside>

                <Separator class="settings-separator" />
            </template>

            <div class="settings-main" :class="{ 'settings-main--wide': $props.hideSidebar }">
                <section class="settings-section">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.settings-container {
    background: #FFFAE9;
    min-height: 100vh;
    padding: 1.5rem;
}

.settings-header {
    margin-bottom: 2rem;
}

.header-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.back-btn {
    background: transparent;
    color: #4b824b;
    border: 2px solid #4b824b;
    border-radius: 10px;
    padding: 0.5rem 0.9rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.back-btn:hover {
    background: rgba(75, 130, 75, 0.1);
}

.settings-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.settings-sidebar {
    width: 100%;
    max-width: 20rem;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.nav-button {
    width: 100%;
    justify-content: flex-start;
    color: #4b824b;
    background: transparent;
    border: 2px solid transparent;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-button:hover {
    background: rgba(75, 130, 75, 0.1);
    border-color: #4b824b;
}

.nav-active {
    color: #FFFAE9 !important;
    background: #4b824b !important;
    border-color: #4b824b !important;
}

.settings-separator {
    background-color: #4b824b;
    height: 2px;
    margin: 1.5rem 0;
}

.settings-main {
    flex: 1;
    max-width: 42rem;
}

.settings-main--wide {
    max-width: 72rem; /* wider when no sidebar (approx 1152px) */
    width: 100%;
    margin: 0 auto; /* center content */
}

.settings-section {
    background: white;
    border: 2px solid #4b824b;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(75, 130, 75, 0.1);
    width: 100%;
}

@media (min-width: 1024px) {
    .settings-content {
        flex-direction: row;
        gap: 3rem;
    }
    
    .settings-sidebar {
        width: 12rem;
        max-width: none;
    }
    
    .settings-separator {
        display: none;
    }
}
</style>
