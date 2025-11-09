<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: '/settings/password',
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="password-settings">
                <HeadingSmall title="Update password" description="Ensure your account is using a long, random password to stay secure" />

                <Form
                    method="put"
                    :action="route('password.update')"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="['password', 'password_confirmation', 'current_password']"
                    class="password-form"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="form-group">
                        <Label for="current_password" class="form-label">Current password</Label>
                        <Input
                            id="current_password"
                            ref="currentPasswordInput"
                            name="current_password"
                            type="password"
                            class="form-input"
                            autocomplete="current-password"
                            placeholder="Current password"
                        />
                        <InputError class="form-error" :message="errors.current_password" />
                    </div>

                    <div class="form-group">
                        <Label for="password" class="form-label">New password</Label>
                        <Input
                            id="password"
                            ref="passwordInput"
                            name="password"
                            type="password"
                            class="form-input"
                            autocomplete="new-password"
                            placeholder="New password"
                        />
                        <InputError class="form-error" :message="errors.password" />
                    </div>

                    <div class="form-group">
                        <Label for="password_confirmation" class="form-label">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="form-input"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        />
                        <InputError class="form-error" :message="errors.password_confirmation" />
                    </div>

                    <div class="form-actions">
                        <Button :disabled="processing" class="save-button brand-button">Save password</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="recentlySuccessful" class="save-success">Saved.</p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
.password-settings {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.password-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
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

/* Label styling */
:deep(.form-label) {
    font-weight: 500 !important;
    color: #4b824b !important;
    font-size: 0.875rem !important;
    margin-bottom: 0.5rem !important;
}

/* Input field styling */
:deep(.form-input) {
    padding: 0.75rem 1rem !important;
    border: 2px solid #4b824b !important;
    border-radius: 12px !important;
    background: #FFFAE9 !important;
    color: #4b824b !important;
    font-size: 1rem !important;
    transition: all 0.2s ease !important;
    width: 100% !important;
    box-shadow: none !important;
}

:deep(.form-input:focus) {
    outline: none !important;
    border-color: #3d6b3d !important;
    box-shadow: 0 0 0 3px rgba(75, 130, 75, 0.1) !important;
}

:deep(.form-input::placeholder) {
    color: #9ca3af !important;
}

.form-error {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #dc2626;
}

.form-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Button styling with high specificity */
:deep(.brand-button),
:deep(button.brand-button) {
    background: #4b824b !important;
    color: #FFFAE9 !important;
    border: 2px solid #4b824b !important;
    padding: 0.75rem 1.5rem !important;
    border-radius: 12px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    box-shadow: none !important;
    text-transform: none !important;
    letter-spacing: normal !important;
}

:deep(.brand-button:hover):not(:disabled),
:deep(button.brand-button:hover):not(:disabled) {
    background: #3d6b3d !important;
    border-color: #3d6b3d !important;
    color: #FFFAE9 !important;
}

:deep(.brand-button:disabled),
:deep(button.brand-button:disabled) {
    background: #9ca3af !important;
    border-color: #9ca3af !important;
    cursor: not-allowed !important;
    color: #ffffff !important;
}

.save-success {
    font-size: 0.875rem;
    color: #4b824b;
    font-weight: 500;
}

/* Override any global button styles */
:deep(button) {
    border-radius: 12px !important;
}
</style>
