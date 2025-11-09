<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';

// Components
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const passwordInput = ref<HTMLInputElement | null>(null);
</script>

<template>
    <div class="delete-user-section">
        <HeadingSmall title="Delete account" description="Delete your account and all of its resources" />
        <div class="delete-warning-box">
            <div class="warning-content">
                <p class="warning-title">Warning</p>
                <p class="warning-text">Please proceed with caution, this cannot be undone.</p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" class="delete-trigger-btn">Delete account</Button>
                </DialogTrigger>
                <DialogContent class="delete-modal">
                    <Form
                        method="delete"
                        :action="route('profile.destroy')"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="delete-form"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="modal-header">
                            <DialogTitle class="modal-title">Are you sure you want to delete your account?</DialogTitle>
                            <DialogDescription class="modal-description">
                                Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your
                                password to confirm you would like to permanently delete your account.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="password-group">
                            <Label for="password" class="sr-only">Password</Label>
                            <Input id="password" type="password" name="password" ref="passwordInput" placeholder="Password" class="password-input" />
                            <InputError :message="errors.password" class="password-error" />
                        </div>

                        <DialogFooter class="modal-footer">
                            <DialogClose as-child>
                                <Button
                                    variant="secondary"
                                    class="cancel-btn"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    Cancel
                                </Button>
                            </DialogClose>

                            <Button type="submit" variant="destructive" :disabled="processing" class="confirm-delete-btn"> Delete account </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>

<style scoped>
.delete-user-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 2px solid #e5e7eb;
}

/* Style the heading section */
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

.delete-warning-box {
    background: #fef2f2;
    border: 2px solid #fca5a5;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.warning-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.warning-title {
    font-weight: 600;
    color: #dc2626;
    font-size: 1rem;
}

.warning-text {
    font-size: 0.875rem;
    color: #b91c1c;
    line-height: 1.5;
}

/* Delete trigger button */
:deep(.delete-trigger-btn) {
    background: #dc2626 !important;
    color: #ffffff !important;
    border: 2px solid #dc2626 !important;
    padding: 0.75rem 1.5rem !important;
    border-radius: 12px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    box-shadow: none !important;
    text-transform: none !important;
    letter-spacing: normal !important;
    width: fit-content !important;
}

:deep(.delete-trigger-btn:hover) {
    background: #b91c1c !important;
    border-color: #b91c1c !important;
    color: #ffffff !important;
}

/* Modal styling */
:deep(.delete-modal) {
    background: #FFFAE9 !important;
    border: 2px solid #4b824b !important;
    border-radius: 12px !important;
}

.delete-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.modal-header {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.modal-title {
    color: #4b824b !important;
    font-weight: 600 !important;
    font-size: 1.125rem !important;
}

.modal-description {
    color: #6b956b !important;
    line-height: 1.5 !important;
}

.password-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

/* Password input in modal */
:deep(.password-input) {
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

:deep(.password-input:focus) {
    outline: none !important;
    border-color: #3d6b3d !important;
    box-shadow: 0 0 0 3px rgba(75, 130, 75, 0.1) !important;
}

:deep(.password-input::placeholder) {
    color: #9ca3af !important;
}

.password-error {
    font-size: 0.875rem;
    color: #dc2626;
}

.modal-footer {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

/* Cancel button */
:deep(.cancel-btn) {
    background: transparent !important;
    color: #4b824b !important;
    border: 2px solid #4b824b !important;
    padding: 0.75rem 1.5rem !important;
    border-radius: 12px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    box-shadow: none !important;
    text-transform: none !important;
    letter-spacing: normal !important;
}

:deep(.cancel-btn:hover) {
    background: #4b824b !important;
    color: #FFFAE9 !important;
}

/* Confirm delete button */
:deep(.confirm-delete-btn) {
    background: #dc2626 !important;
    color: #ffffff !important;
    border: 2px solid #dc2626 !important;
    padding: 0.75rem 1.5rem !important;
    border-radius: 12px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    box-shadow: none !important;
    text-transform: none !important;
    letter-spacing: normal !important;
}

:deep(.confirm-delete-btn:hover):not(:disabled) {
    background: #b91c1c !important;
    border-color: #b91c1c !important;
}

:deep(.confirm-delete-btn:disabled) {
    background: #9ca3af !important;
    border-color: #9ca3af !important;
    cursor: not-allowed !important;
}

/* Override any global button styles */
:deep(button) {
    border-radius: 12px !important;
}
</style>
