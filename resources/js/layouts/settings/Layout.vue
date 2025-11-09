<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

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
</script>

<template>
    <div class="settings-container">
        <div class="settings-header">
            <Heading title="Settings" description="Manage your profile and account settings" />
        </div>

        <div class="settings-content">
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

            <div class="settings-main">
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

.settings-section {
    background: white;
    border: 2px solid #4b824b;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(75, 130, 75, 0.1);
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
