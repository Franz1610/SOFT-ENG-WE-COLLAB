<template>
  <div class="auth-page">
    <div class="auth-page__overlay" aria-hidden="true"></div>
    <AppHeader :user="null" active="register" @auth="handleAuthAction" />
    <main class="main-content flex items-center justify-center min-h-screen">
      <div class="signup-card">
        <h1 class="text-2xl font-semibold text-center text-[#2f3b2d] mb-4">Sign up</h1>
        <Form
          method="post"
          :action="route('register')"
          v-slot="{ errors, processing }"
          class="w-full flex flex-col gap-4"
        >
          <div class="space-y-2">
            <label for="name" class="form-label">Name</label>
            <input id="name" name="name" type="text" required class="form-input" />
            <div v-if="errors.name" class="text-red-500 text-xs">{{ errors.name }}</div>
          </div>
          <div class="space-y-2">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" required class="form-input" />
            <div v-if="errors.email" class="text-red-500 text-xs">{{ errors.email }}</div>
          </div>
          <div class="space-y-2">
            <label for="contact_mask" class="form-label">Contact Number</label>
            <input
              id="contact_mask"
              type="text"
              inputmode="numeric"
              autocomplete="tel"
              placeholder="09XX XXX XXXX"
              class="form-input"
              @input="handleContactInput"
              @blur="handleContactBlur"
            />
            <input type="hidden" name="contact" :value="contactDigits" />
            <div v-if="errors.contact" class="text-red-500 text-xs">{{ errors.contact }}</div>
          </div>
          <div class="password-group">
            <div class="space-y-2">
              <label for="password" class="form-label">Password</label>
              <input id="password" name="password" type="password" required class="form-input" />
              <div v-if="errors.password" class="text-red-500 text-xs">{{ errors.password }}</div>
            </div>
            <div class="space-y-2">
              <label for="password_confirmation" class="form-label">Confirm Password</label>
              <input id="password_confirmation" name="password_confirmation" type="password" required class="form-input" />
              <div v-if="errors.password_confirmation" class="text-red-500 text-xs">{{ errors.password_confirmation }}</div>
            </div>
          </div>
          <button type="submit" class="signup-btn" :disabled="processing">Sign up</button>
        </Form>
        <div class="section-divider" aria-hidden="true"></div>
        <div class="mt-3 text-center text-xs text-[#2f3b2d] uppercase tracking-wide">
          Already have an account?
          <a href="/login" class="text-[#3d74c0] font-semibold hover:underline">Sign in now!</a>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AppHeader from '@/components/AppHeader.vue';

const handleAuthAction = () => {};
import { ref } from 'vue';

const contactDigits = ref('');

function formatContact(value: string) {
  const digits = value.replace(/\D/g, '').slice(0, 11);
  const parts = [] as string[];
  if (digits.length > 0) parts.push(digits.slice(0, 4));
  if (digits.length > 4) parts.push(digits.slice(4, 7));
  if (digits.length > 7) parts.push(digits.slice(7, 11));
  return parts.filter(Boolean).join(' ');
}

function handleContactInput(event: Event) {
  const target = event.target as HTMLInputElement | null;
  if (!target) return;
  contactDigits.value = target.value.replace(/\D/g, '').slice(0, 11);
  target.value = formatContact(contactDigits.value);
}

function handleContactBlur(event: Event) {
  const target = event.target as HTMLInputElement | null;
  if (!target) return;
  target.value = formatContact(contactDigits.value);
}
</script>

<style scoped>
.auth-page {
  position: relative;
  min-height: 100vh;
  width: 100vw;
  background: url('/images/homepage/hero.png') center/cover no-repeat fixed;
}
.auth-page__overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: brightness(0.8);
}
main.main-content {
  min-height: 100vh;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  margin-top: 54px;
  position: relative;
  z-index: 1;
  padding: 2rem 1rem 3rem;
}
.signup-card {
  width: 100%;
  max-width: 20rem;
  background: rgba(255, 255, 255, 0.96);
  border-radius: 1.2rem;
  padding: 1.75rem 1.5rem;
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15), 0 3px 10px rgba(0, 0, 0, 0.08);
}
.form-label {
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #2f3b2d;
}
.form-input {
  width: 100%;
  border: 1px solid #d9ded6;
  border-radius: 0.65rem;
  padding: 0.7rem 0.9rem;
  font-size: 0.9rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.form-input:focus {
  outline: none;
  border-color: #7aa36e;
  box-shadow: 0 0 0 3px rgba(122, 163, 110, 0.25);
}
.password-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 0.9rem;
  border: 1px solid #e4e9e1;
  border-radius: 0.85rem;
  background: rgba(244, 247, 242, 0.92);
}
.signup-btn {
  width: 100%;
  padding: 0.85rem;
  border-radius: 0.75rem;
  font-size: 0.95rem;
  font-weight: 600;
  letter-spacing: 0.035em;
  text-transform: uppercase;
  background: linear-gradient(135deg, #2f3b2d, #4b824b);
  color: #fff;
  box-shadow: 0 10px 20px rgba(55, 92, 64, 0.3);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.signup-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.signup-btn:not(:disabled):hover {
  transform: translateY(-1px);
  box-shadow: 0 14px 26px rgba(55, 92, 64, 0.45);
}
.section-divider {
  width: 100%;
  height: 1px;
  background: linear-gradient(90deg, rgba(73, 88, 70, 0), rgba(73, 88, 70, 0.2), rgba(73, 88, 70, 0));
  margin-top: 1rem;
}

@media (max-width: 480px) {
  .signup-card {
    padding: 1.5rem 1.25rem;
  }
}
</style>
