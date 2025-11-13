<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AppHeader from '@/components/AppHeader.vue';

// On the login page, we don't need special auth handling.
const handleAuthAction = () => {};
</script>

<template>
  <div style="background: url('/images/homepage/hero.png') center/cover no-repeat fixed; min-height: 100vh; width: 100vw;">
    <AppHeader :user="null" active="login" @auth="handleAuthAction" />
    <main class="main-content flex items-center justify-center min-h-screen" style="background: rgba(0,0,0,0.55);">
      <div class="login-card bg-white bg-opacity-90 rounded-2xl shadow-lg p-10 w-full max-w-md flex flex-col items-center">
        <h1 class="text-4xl font-bold mb-6 text-center">Login</h1>
        <Form method="post" :action="route('login')" v-slot="{ errors, processing }" class="w-full flex flex-col gap-5">
          <div>
            <label for="email" class="block font-semibold mb-1">Email</label>
            <input id="email" name="email" type="email" required class="w-full border rounded px-3 py-2" />
            <div v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</div>
          </div>
          <div>
            <label for="password" class="block font-semibold mb-1">Password</label>
            <input id="password" name="password" type="password" required class="w-full border rounded px-3 py-2" />
            <div v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</div>
          </div>
          <div class="flex items-center justify-between w-full mb-2">
            <label class="flex items-center gap-2">
              <input type="checkbox" name="remember" />
              <span>Remember me</span>
            </label>
            <a href="/forgot-password" class="text-sm text-neutral-700 hover:underline">Forgot Password?</a>
          </div>
          <button type="submit" class="w-full py-2 rounded bg-black text-white font-semibold text-lg hover:bg-neutral-800 transition" :disabled="processing">Login</button>
        </Form>
        <div class="mt-4 text-center text-black">
          Don't have an account? <a href="/register" class="text-blue-700 hover:underline">Register now!</a>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
main.main-content {
  min-height: 100vh;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  /* Match homepage spacing under the fixed header */
  margin-top: 54px;
}
.login-card {
  box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.10);
}
</style>