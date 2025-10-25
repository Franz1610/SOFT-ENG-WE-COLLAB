<template>
  <div style="background: url('/images/homepage/hero.png') center/cover no-repeat fixed; min-height: 100vh; width: 100vw;">
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo">WECOLLAB</div>
        <nav class="nav">
          <a href="/login" class="nav-link">Log in</a>
          <a href="#" class="nav-link">Deals & Promo</a>
          <a href="/whats-new" class="nav-link">What's NEW?</a>
          <a href="#" class="nav-link">Booking</a>
          <button class="home-btn" @click="goHome">HOME</button>
        </nav>
      </div>
    </header>
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
            <input id="contact" name="contact" type="text" required class="w-full border rounded px-3 py-1.5 text-sm" />
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
function goHome() {
  window.location.href = '/';
}
</script>

<style scoped>
.header {
  background: #495846;
  color: #fff;
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  z-index: 100;
  box-shadow: 0 1px 6px rgba(0,0,0,0.03);
}
.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0.5rem 2rem;
  min-height: 54px;
  width: 100vw;
}
.logo {
  font-weight: bold;
  letter-spacing: 0.1em;
  font-size: 1.5rem;
  line-height: 1;
}
.nav {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}
.nav-link {
  color: #fff;
  text-decoration: none;
  font-size: 1rem;
  padding: 0.3rem 0.7rem;
  border-radius: 6px;
  transition: background 0.2s, color 0.2s;
  line-height: 1.2;
}
.nav-link:hover {
  background: #fff;
  color: #495846;
}
.nav-link.active {
  background: #fff;
  color: #495846;
  font-weight: 600;
}
.home-btn {
  background: transparent;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 0.3rem 1rem;
  font-size: 1rem;
  font-weight: 400;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  margin-left: 0.5rem;
  line-height: 1.2;
}
.home-btn:hover {
  background: #fff;
  color: #495846;
}
main.main-content {
  min-height: 100vh;
  width: 100vw;
  max-width: 100vw;
  overflow-x: hidden;
  margin-top: 54px;
}
.signup-card {
  box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.10);
}
</style>
