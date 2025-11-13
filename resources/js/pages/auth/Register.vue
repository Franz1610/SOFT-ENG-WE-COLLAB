<template>
  <div style="background: url('/images/homepage/hero.png') center/cover no-repeat fixed; min-height: 100vh; width: 100vw;">
    <AppHeader :user="null" active="register" @auth="handleAuthAction" />
    <main class="main-content flex items-center justify-center min-h-screen" style="background: rgba(0,0,0,0.55);">
      <div class="signup-card bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 w-full max-w-sm flex flex-col items-center">
        <h1 class="text-3xl font-bold mb-4 text-center">Sign up</h1>
        <Form method="post" :action="route('register')" v-slot="{ errors, processing }" class="w-full flex flex-col gap-3">
          <div>
            <label for="name" class="block font-semibold mb-1">Name</label>
            <input id="name" name="name" type="text" required class="w-full border rounded px-3 py-1.5 text-sm" />
            <div v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</div>
          </div>
          <div>
            <label for="email" class="block font-semibold mb-1">Email</label>
            <input id="email" name="email" type="email" required class="w-full border rounded px-3 py-1.5 text-sm" />
            <div v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</div>
          </div>
          <div>
            <label for="contact" class="block font-semibold mb-1">Contact Number</label>
            <input
              id="contact"
              name="contact"
              type="tel"
              inputmode="numeric"
              pattern="[0-9]{11}"
              maxlength="11"
              required
              class="w-full border rounded px-3 py-1.5 text-sm"
              @input="sanitizeContactInput"
            />
            <div v-if="errors.contact" class="text-red-500 text-xs mt-1">{{ errors.contact }}</div>
          </div>
          <div>
            <label for="password" class="block font-semibold mb-1">Password</label>
            <input id="password" name="password" type="password" required class="w-full border rounded px-3 py-1.5 text-sm" />
            <div v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</div>
          </div>
          <div>
            <label for="password_confirmation" class="block font-semibold mb-1">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full border rounded px-3 py-1.5 text-sm" />
            <div v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ errors.password_confirmation }}</div>
          </div>
          <div class="flex items-center mb-2">
            <input type="checkbox" name="remember" id="remember" class="mr-2" />
            <label for="remember" class="text-sm">Remember me</label>
          </div>
          <button type="submit" class="w-full py-2 rounded bg-black text-white font-semibold text-base hover:bg-neutral-800 transition"
            :disabled="processing">Sign up</button>
        </Form>
        <div class="mt-3 text-center text-black text-sm">
          Already have an account? <a href="/login" class="text-blue-700 hover:underline">Sign in now!</a>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AppHeader from '@/components/AppHeader.vue';

const handleAuthAction = () => {};

function sanitizeContactInput(event: Event) {
  const target = event.target as HTMLInputElement | null;
  if (!target) return;
  target.value = target.value.replace(/[^0-9]/g, '').slice(0, 11);
}
</script>

<style scoped>
main.main-content {
  min-height: 100vh;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  margin-top: 54px;
}
.signup-card {
  box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.10);
}
</style>
