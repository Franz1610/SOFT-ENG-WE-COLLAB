<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user as User;
const isAdmin = computed(() => ['admin', 'admin_officer'].includes((user?.role as any) ?? ''));
</script>

<template>
    <component :is="isAdmin ? 'div' : AppHeaderLayout" :active="isAdmin ? undefined : 'home'">
        <Head title="Profile settings" />

        <SettingsLayout :hide-sidebar="!isAdmin">
            <div class="profile-settings">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <Form method="patch" :action="route('profile.update')" class="profile-form" v-slot="{ errors, processing, recentlySuccessful }">
                    <div class="form-group">
                        <Label for="name" class="form-label">Name</Label>
                        <Input
                            id="name"
                            class="form-input"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="form-error" :message="errors.name" />
                    </div>

                    <div class="form-group">
                        <Label for="email" class="form-label">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="form-input"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="form-error" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && user && user.email_verified_at === null" class="verification-notice">
                        <p class="verification-text">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="verification-link"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="verification-success">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="form-actions">
                        <Button :disabled="processing" class="save-button brand-button">Save</Button>

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

            <DeleteUser />
        </SettingsLayout>
    </component>
</template>

<style scoped>
.profile-settings {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
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
    padding: 1rem !important;
    border-radius: 10px !important;
    margin-bottom: 0.75rem !important;
}

:deep(.heading-small-title) {
    color: #FFFAE9 !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    margin-bottom: 0.25rem !important;
}

:deep(.heading-small-description) {
    color: #FFFAE9 !important;
    opacity: 0.95 !important;
    font-size: 0.8rem !important;
}

/* Label styling */
:deep(.form-label) {
    font-weight: 500 !important;
    color: #4b824b !important;
    font-size: 0.8rem !important;
    margin-bottom: 0.35rem !important;
}

/* Input field styling */
:deep(.form-input) {
    padding: 0.5rem 0.75rem !important;
    border: 2px solid #4b824b !important;
    border-radius: 10px !important;
    background: #FFFAE9 !important;
    color: #4b824b !important;
    font-size: 0.95rem !important;
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

.verification-notice {
    background: #fef3c7;
    border: 2px solid #f59e0b;
    border-radius: 12px;
    padding: 1rem;
    margin: -1rem 0;
}

.verification-text {
    font-size: 0.875rem;
    color: #92400e;
    margin-bottom: 0.5rem;
}

.verification-link {
    color: #4b824b !important;
    text-decoration: underline;
    text-underline-offset: 4px;
    transition: color 0.2s ease;
    background: none !important;
    border: none !important;
    cursor: pointer;
    font-weight: 500;
}

.verification-link:hover {
    color: #3d6b3d !important;
}

.verification-success {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #065f46;
}

.form-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* Button styling with high specificity */
:deep(.brand-button),
:deep(button.brand-button) {
    background: #4b824b !important;
    color: #FFFAE9 !important;
    border: 2px solid #4b824b !important;
    padding: 0.55rem 1rem !important;
    border-radius: 10px !important;
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
    border-radius: 10px !important;
}

/* Reduce card padding for this page so content fits without scrolling */
:deep(.settings-section) {
    padding: 1.25rem !important;
}
</style>