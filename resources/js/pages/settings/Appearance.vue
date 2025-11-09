<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="appearance-settings">
                <HeadingSmall title="Appearance settings" description="Your application uses a consistent color scheme matching the WeCollab brand" />
                
                <div class="theme-info">
                    <h3 class="theme-title">Current Theme</h3>
                    <p class="theme-description">Your application uses the WeCollab brand colors for a consistent experience across all pages:</p>
                    <ul class="color-list">
                        <li class="color-item">
                            <span class="color-swatch color-cream"></span>
                            <strong>Primary Background:</strong> Cream (#FFFAE9)
                        </li>
                        <li class="color-item">
                            <span class="color-swatch color-green"></span>
                            <strong>Primary Text:</strong> Forest Green (#4b824b)
                        </li>
                        <li class="color-item">
                            <span class="color-swatch color-green"></span>
                            <strong>Accent Background:</strong> Forest Green (#4b824b)
                        </li>
                        <li class="color-item">
                            <span class="color-swatch color-cream"></span>
                            <strong>Accent Text:</strong> Cream (#FFFAE9)
                        </li>
                    </ul>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
.appearance-settings {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Heading sections with better styling */
:deep(.heading-small-container) {
    background: #4b824b !important;
    color: #FFFAE9 !important;
    padding: 1.5rem !important;
    border-radius: 12px !important;
    margin-bottom: 1.5rem !important;
}

:deep(.heading-small-title) {
    color: #FFFAE9 !important;
    font-size: 1.25rem !important;
    font-weight: 600 !important;
    margin-bottom: 0.5rem !important;
}

:deep(.heading-small-description) {
    color: #FFFAE9 !important;
    opacity: 0.9 !important;
    font-size: 0.875rem !important;
}

.theme-info {
    background: #4b824b;
    color: #FFFAE9;
    padding: 2rem;
    border-radius: 12px;
    border: 2px solid #4b824b;
    box-shadow: 0 4px 12px rgba(75, 130, 75, 0.1);
}

.theme-title {
    font-size: 1.375rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #FFFAE9;
}

.theme-description {
    margin-bottom: 1.5rem;
    line-height: 1.6;
    color: #FFFAE9;
}

.color-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-left: 0;
    padding-left: 0;
    list-style: none;
}

.color-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    line-height: 1.5;
    color: #FFFAE9;
}

.color-swatch {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 6px;
    border: 2px solid #FFFAE9;
    flex-shrink: 0;
}

.color-cream {
    background-color: #FFFAE9;
}

.color-green {
    background-color: #3d6b3d;
}
</style>