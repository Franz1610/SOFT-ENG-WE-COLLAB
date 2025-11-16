<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AppHeader from '@/components/AppHeader.vue';

// On the login page, we don't need special auth handling.
const handleAuthAction = () => {};
</script>

<template>
  <div class="login-page">
    <div class="login-page__backdrop" aria-hidden="true"></div>
    <AppHeader :user="null" active="login" @auth="handleAuthAction" />
    <main class="main-content flex items-center justify-center min-h-screen">
      <div class="login-card">
        <h1 class="text-3xl font-semibold mb-4 text-center text-[#3a4d33]">Login</h1>
        <Form
          method="post"
          :action="route('login')"
          v-slot="{ errors, processing }"
          class="w-full flex flex-col gap-6"
        >
          <div class="space-y-2">
            <label for="email" class="block text-sm font-semibold tracking-wide text-[#2f3b2d]">Email</label>
            <input
              id="email"
              name="email"
              type="email"
              required
              class="w-full border border-[#d9ded6] rounded-lg px-3 py-3 focus:outline-none focus:ring-2 focus:ring-[#7aa36e]"
            />
            <div v-if="errors.email" class="text-red-500 text-xs">{{ errors.email }}</div>
          </div>
          <div class="space-y-2">
            <label for="password" class="block text-sm font-semibold tracking-wide text-[#2f3b2d]">Password</label>
            <input
              id="password"
              name="password"
              type="password"
              required
              class="w-full border border-[#d9ded6] rounded-lg px-3 py-3 focus:outline-none focus:ring-2 focus:ring-[#7aa36e]"
            />
            <div v-if="errors.password" class="text-red-500 text-xs">{{ errors.password }}</div>
          </div>
          <div class="grid grid-cols-2 gap-3 text-sm text-[#2f3b2d] items-center">
            <label class="flex items-center gap-2 font-medium">
              <input type="checkbox" name="remember" class="accent-[#4b824b]" />
              <span>Remember me</span>
            </label>
            <a href="/forgot-password" class="text-right font-medium text-[#4b824b] hover:underline">Forgot Password?</a>
          </div>
          <button
            type="submit"
            class="login-btn"
            :disabled="processing"
          >
            Login
          </button>
        </Form>
        <div class="mt-5 text-center text-sm text-[#2f3b2d]">
          Don't have an account?
          <a href="/register" class="text-[#3d74c0] font-semibold hover:underline">Register now!</a>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.login-page {
  position: relative;
  min-height: 100vh;
  width: 100vw;
  background: url('/images/homepage/hero.png') center/cover no-repeat fixed;
}
.login-page__backdrop {
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
.login-card {
  width: 100%;
  max-width: 24rem;
  background: rgba(255, 255, 255, 0.97);
  border-radius: 1.5rem;
  padding: 2.5rem 2rem;
  box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18), 0 4px 20px rgba(0, 0, 0, 0.08);
}
.login-btn {
  width: 100%;
  padding: 0.95rem;
  border-radius: 0.75rem;
  font-size: 1.05rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  background: linear-gradient(135deg, #2f3b2d, #4b824b);
  color: #fff;
  box-shadow: 0 12px 24px rgba(55, 92, 64, 0.35);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.login-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.login-btn:not(:disabled):hover {
  transform: translateY(-1px);
  box-shadow: 0 14px 26px rgba(55, 92, 64, 0.45);
}
</style>