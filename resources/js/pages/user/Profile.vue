<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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

const props = defineProps<Props>();

const page = usePage();
const user = page.props.auth.user as User;
const isUnverified = computed(() => user && user.email_verified_at === null);

// Derive default first/last name from stored full name
const defaultFirstName = computed(() => {
  const full = (user?.name || '').trim();
  if (!full) return '';
  const parts = full.split(/\s+/);
  if (parts.length === 1) return parts[0];
  return parts.slice(0, parts.length - 1).join(' ');
});

const defaultLastName = computed(() => {
  const full = (user?.name || '').trim();
  if (!full) return '';
  const parts = full.split(/\s+/);
  if (parts.length === 1) return '';
  return parts[parts.length - 1];
});

function onContactInput(event: Event) {
  const target = event.target as HTMLInputElement | null;
  if (!target) return;
  target.value = target.value.replace(/[^0-9]/g, '').slice(0, 11);
}
</script>

<template>
  <AppHeaderLayout :active="'home'">
    <Head title="Your Profile" />

    <SettingsLayout :hide-sidebar="true">
      <div class="profile-settings">
        <HeadingSmall
          title="Profile information"
          description="Update your name and phone number. Email is read-only."
        />

        <Form
          method="patch"
          :action="route('user.profile.update')"
          class="profile-form"
          v-slot="{ errors, processing, recentlySuccessful }"
        >
          <div class="form-group">
            <Label class="form-label">Name</Label>
            <div style="display:flex; gap:0.75rem; width:100%;">
              <div style="flex:1;">
                <Input
                  id="first_name"
                  class="form-input"
                  name="first_name"
                  :default-value="defaultFirstName"
                  required
                  autocomplete="given-name"
                  placeholder="First name"
                />
                <InputError class="form-error" :message="errors.first_name" />
              </div>
              <div style="flex:1;">
                <Input
                  id="last_name"
                  class="form-input"
                  name="last_name"
                  :default-value="defaultLastName"
                  required
                  autocomplete="family-name"
                  placeholder="Last name"
                />
                <InputError class="form-error" :message="errors.last_name" />
              </div>
            </div>
          </div>

          <div class="form-group">
            <Label for="contact" class="form-label">Phone number</Label>
            <Input
              id="contact"
              class="form-input"
              name="contact"
              :default-value="user.contact || ''"
              type="tel"
              inputmode="numeric"
              pattern="[0-9]{11}"
              maxlength="11"
              @input="onContactInput"
              autocomplete="tel"
              placeholder="e.g. 0917 123 4567"
            />
            <InputError class="form-error" :message="errors.contact" />
          </div>

          <div class="form-group">
            <Label for="email" class="form-label">Email address</Label>
            <Input
              id="email"
              type="email"
              class="form-input"
              name="email"
              :default-value="user.email"
              readonly
              autocomplete="username"
            />
          </div>

          <div v-if="props.mustVerifyEmail && isUnverified" class="verification-notice">
            <p class="verification-text">
              Your email address is unverified.
              <Link :href="route('verification.send')" method="post" as="button" class="verification-link">
                Click here to resend the verification email.
              </Link>
            </p>

            <div v-if="props.status === 'verification-link-sent'" class="verification-success">
              A new verification link has been sent to your email address.
            </div>
          </div>

          <div class="form-actions">
            <Button :disabled="processing" class="brand-button">Save</Button>

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

        <HeadingSmall
          title="Change password"
          description="Use a long, random password to keep your account secure"
        />

        <Form
          method="put"
          :action="route('password.update')"
          :options="{ preserveScroll: true }"
          reset-on-success
          :reset-on-error="['password', 'password_confirmation', 'current_password']"
          class="password-form"
          v-slot="{ errors, processing, recentlySuccessful }"
        >
          <div class="form-group">
            <Label for="current_password" class="form-label">Current password</Label>
            <Input
              id="current_password"
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
            <Button :disabled="processing" class="brand-button">Save password</Button>

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
  </AppHeaderLayout>
</template>

<style scoped>
.profile-settings {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
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

:deep(.form-input[readonly]) {
  background: #f6f7f1 !important;
  cursor: not-allowed !important;
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
